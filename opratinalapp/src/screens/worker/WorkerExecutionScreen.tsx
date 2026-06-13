import React, { useState } from 'react';
import { Alert, ScrollView, StyleSheet, Text, View } from 'react-native';
import { postWorkerAction, uploadWorkerPhoto } from '../../api/workerApi';
import { AppButton } from '../../components/AppButton';
import { ImageUploadBox, PhotoMap, PhotoSide } from '../../components/ImageUploadBox';
import { enqueuePhotoUploads } from '../../services/offlineUploadQueue';
import { getCurrentCoords } from '../../services/locationTracking';

const REQUIRED: PhotoSide[] = ['front', 'back', 'left', 'right'];

export const WorkerExecutionScreen = ({ route, navigation }: any) => {
  const { job, action } = route.params;
  const [photos, setPhotos] = useState<PhotoMap>({});
  const [loading, setLoading] = useState(false);

  const missing = REQUIRED.filter((side) => !photos[side]);

  const handleSubmit = async () => {
    if (missing.length) {
      Alert.alert('Photos Required', `Add ${missing.join(', ')} photos before continuing.`);
      return;
    }

    setLoading(true);
    try {
      const entries = REQUIRED.map((side) => ({ side, uri: photos[side]! }));

      for (const item of entries) {
        await uploadWorkerPhoto(job.id, action.photoType, item.side, item.uri);
      }

      const coords = await getCurrentCoords().catch(() => ({}));
      await postWorkerAction(action.api, coords);
      Alert.alert('Success', 'Proof uploaded and job moved to the next step.');
      navigation.goBack();
    } catch (error: any) {
      if (!error?.response) {
        await enqueuePhotoUploads(
          REQUIRED.map((side) => ({
            id: `${job.id}-${action.key}-${side}-${Date.now()}`,
            bookingId: Number(job.id),
            role: 'worker',
            action: action.key === 'upload_before_start' ? 'start_service' : 'complete_service',
            api: `/worker/jobs/${job.id}/media`,
            actionApi: action.api,
            photoType: action.photoType,
            side,
            localUri: photos[side]!,
            createdAt: new Date().toISOString(),
            retryCount: 0,
          }))
        );
        Alert.alert('Saved Offline', 'Photos saved offline. They will upload automatically when internet is back.');
      } else {
        Alert.alert('Error', error.response?.data?.message || 'Failed to submit proof.');
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
      <ImageUploadBox title="Required Photo Proof" photoMap={photos} onPhotoMapChange={setPhotos} requiredSides={REQUIRED} />
      <AppButton title={action.label} onPress={handleSubmit} loading={loading} />
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#FFFFFF', padding: 16 },
  header: { marginBottom: 8 },
  title: { fontSize: 20, fontWeight: '800', color: '#111827' },
  subtitle: { marginTop: 4, fontSize: 14, color: '#64748B' },
});
