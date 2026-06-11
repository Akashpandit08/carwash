import React, { useState } from 'react';
import { ScrollView, StyleSheet, Alert, Platform } from 'react-native';
import { updateJobStatus, uploadBeforeImages, uploadAfterImages } from '../../api/workerApi';
import { AppButton } from '../../components/AppButton';
import { ImageUploadBox } from '../../components/ImageUploadBox';

export const WorkerExecutionScreen = ({ route, navigation }: any) => {
  const { job, nextStatus } = route.params;
  const bookingId = job.id;

  const [images, setImages] = useState<string[]>([]);
  const [loading, setLoading] = useState(false);

  const isBefore = nextStatus === 'service_started';
  const isAfter = nextStatus === 'service_completed' || nextStatus === 'ready_for_delivery';

  const createFormData = (uris: string[], fieldName: string) => {
    const formData = new FormData();
    uris.forEach((uri, index) => {
      formData.append(`${fieldName}[${index}]`, {
        uri: Platform.OS === 'android' ? uri : uri.replace('file://', ''),
        type: 'image/jpeg',
        name: `image_${index}.jpg`,
      } as any);
    });
    return formData;
  };

  const handleUpload = async () => {
    if (images.length === 0) return Alert.alert('Error', 'Please select images');
    setLoading(true);
    try {
      if (isBefore) {
        const data = createFormData(images, 'before_images');
        await uploadBeforeImages(bookingId, data);
      } else {
        const data = createFormData(images, 'after_images');
        await uploadAfterImages(bookingId, data);
      }
      await updateJobStatus(bookingId, nextStatus);
      Alert.alert('Success', `Images uploaded and status updated!`);
      navigation.goBack();
    } catch (e: any) {
      Alert.alert('Error', e.response?.data?.message || 'Failed to update status');
    } finally {
      setLoading(false);
    }
  };

  return (
    <ScrollView style={styles.container}>
      <ImageUploadBox 
        title={isBefore ? "Before Service Images" : "After Service Images"} 
        images={images} 
        onImagesChange={setImages} 
      />
      <AppButton title={isBefore ? "Start Service" : "Complete Service"} onPress={handleUpload} loading={loading} />
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#FFF', padding: 16 },
});
