import React, { useState } from 'react';
import { ScrollView, StyleSheet, Alert, Platform } from 'react-native';
import { updateJobStatus, uploadPickupImages } from '../../api/pickupDriverApi';
import { AppButton } from '../../components/AppButton';
import { ImageUploadBox } from '../../components/ImageUploadBox';

export const PickupExecutionScreen = ({ route, navigation }: any) => {
  const { job, nextStatus } = route.params;
  const bookingId = job.id;

  const [images, setImages] = useState<string[]>([]);
  const [loading, setLoading] = useState(false);

  const createFormData = (uris: string[], fieldName: string) => {
    const formData = new FormData();
    uris.forEach((uri, index) => {
      formData.append(`${fieldName}[${index}]`, {
        uri: Platform.OS === 'android' ? uri : uri.replace('file://', ''),
        type: 'image/jpeg',
        name: `pickup_${index}.jpg`,
      } as any);
    });
    return formData;
  };

  const handlePickup = async () => {
    if (images.length === 0) return Alert.alert('Error', 'Please upload vehicle images at pickup');
    setLoading(true);
    try {
      const data = createFormData(images, 'pickup_images');
      await uploadPickupImages(bookingId, data);
      await updateJobStatus(bookingId, nextStatus || 'car_picked_up');
      Alert.alert('Success', 'Vehicle picked up and images uploaded!');
      navigation.goBack();
    } catch (e: any) {
      Alert.alert('Error', e.response?.data?.message || 'Failed to submit pickup details');
    } finally {
      setLoading(false);
    }
  };

  return (
    <ScrollView style={styles.container}>
      <ImageUploadBox 
        title="Upload Vehicle condition images before driving" 
        images={images} 
        onImagesChange={setImages} 
      />
      <AppButton title="Confirm Pickup" onPress={handlePickup} loading={loading} />
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#FFF', padding: 16 },
});
