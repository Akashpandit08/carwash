import React, { useState } from 'react';
import { ScrollView, StyleSheet, Alert, Platform } from 'react-native';
import { updateJobStatus, uploadDeliveryImages } from '../../api/pickupDriverApi';
import { AppButton } from '../../components/AppButton';
import { ImageUploadBox } from '../../components/ImageUploadBox';

export const DeliveryExecutionScreen = ({ route, navigation }: any) => {
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
        name: `delivery_${index}.jpg`,
      } as any);
    });
    return formData;
  };

  const handleDelivery = async () => {
    if (images.length === 0) return Alert.alert('Error', 'Please upload delivery images');
    setLoading(true);
    try {
      const data = createFormData(images, 'delivery_images');
      await uploadDeliveryImages(bookingId, data);
      await updateJobStatus(bookingId, nextStatus || 'delivered');
      Alert.alert('Success', 'Vehicle delivered and images uploaded!');
      navigation.goBack();
    } catch (e: any) {
      Alert.alert('Error', e.response?.data?.message || 'Failed to submit delivery details');
    } finally {
      setLoading(false);
    }
  };

  return (
    <ScrollView style={styles.container}>
      <ImageUploadBox 
        title="Upload Vehicle condition images at drop-off" 
        images={images} 
        onImagesChange={setImages} 
      />
      <AppButton title="Confirm Delivery" onPress={handleDelivery} loading={loading} />
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#FFF', padding: 16 },
});
