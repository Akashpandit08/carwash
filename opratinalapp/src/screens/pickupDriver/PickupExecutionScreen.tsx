import React, { useState } from 'react';
import { Alert, ScrollView, StyleSheet, Text, View } from 'react-native';
import { postDriverAction, uploadDriverPhoto } from '../../api/pickupDriverApi';
import { AppButton } from '../../components/AppButton';
import { ImageUploadBox, PhotoMap, PhotoSide } from '../../components/ImageUploadBox';
import { enqueuePhotoUploads } from '../../services/offlineUploadQueue';

const REQUIRED: PhotoSide[] = ['front', 'back', 'left', 'right'];

export const PickupExecutionScreen = ({ route, navigation }: any) => {
  const { job, action } = route.params;
  const [photos, setPhotos] = useState<PhotoMap>({});
  const [loading, setLoading] = useState(false);
  const missing = REQUIRED.filter((side) => !photos[side]);

  const handlePickup = async () => {
    if (missing.length) return Alert.alert('Photos Required', `Add ${missing.join(', ')} photos before continuing.`);

    setLoading(true);
    try {
      for (const side of REQUIRED) {
        await uploadDriverPhoto(job.id, action.photoType, side, photos[side]!);
      }
      await postDriverAction(action.api, { notes: 'Vehicle picked up with key' });
      Alert.alert('Success', 'Pickup proof submitted.');
      navigation.goBack();
    } catch (error: any) {
      if (!error?.response) {
        await enqueuePhotoUploads(REQUIRED.map((side) => ({
          id: `${job.id}-${action.key}-${side}-${Date.now()}`,
          bookingId: Number(job.id),
          role: 'pickup_driver',
          action: 'pickup_vehicle',
          api: `/pickup-driver/jobs/${job.id}/media`,
          actionApi: action.api,
          photoType: action.photoType,
          side,
          localUri: photos[side]!,
          createdAt: new Date().toISOString(),
          retryCount: 0,
        })));
        Alert.alert('Saved Offline', 'Photos saved offline. They will upload automatically when internet is back.');
      } else {
        Alert.alert('Error', error.response?.data?.message || 'Failed to submit pickup proof.');
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <ScrollView style={styles.container}>
      <View style={styles.header}>
        <Text style={styles.title}>{action.label}</Text>
        <Text style={styles.subtitle}>Booking #{job.booking_number || job.booking_no || job.id}</Text>
      </View>
      <ImageUploadBox title="Vehicle Condition Proof" photoMap={photos} onPhotoMapChange={setPhotos} requiredSides={REQUIRED} />
      <AppButton title={action.label} onPress={handlePickup} loading={loading} />
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#FFFFFF', padding: 16 },
  header: { marginBottom: 8 },
  title: { fontSize: 20, fontWeight: '800', color: '#111827' },
  subtitle: { marginTop: 4, fontSize: 14, color: '#64748B' },
});
