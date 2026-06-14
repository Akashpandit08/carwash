import React from 'react';
import { Alert, Image, StyleSheet, Text, TouchableOpacity, View } from 'react-native';
import * as ImagePicker from 'expo-image-picker';

export type PhotoSide = 'front' | 'back' | 'left' | 'right' | 'extra';
export type PhotoMap = Partial<Record<PhotoSide, string>>;

interface ImageUploadBoxProps {
  title: string;
  images?: string[];
  onImagesChange?: (images: string[]) => void;
  photoMap?: PhotoMap;
  onPhotoMapChange?: (photos: PhotoMap) => void;
  requiredSides?: PhotoSide[];
  maxImages?: number;
}

export const ImageUploadBox: React.FC<ImageUploadBoxProps> = ({
  title,
  images = [],
  onImagesChange,
  photoMap,
  onPhotoMapChange,
  requiredSides = ['front', 'back', 'left', 'right'],
  maxImages = 5,
}) => {
  const takePhoto = async (side?: PhotoSide) => {
    const permission = await ImagePicker.requestCameraPermissionsAsync();
    if (!permission.granted) {
      Alert.alert('Camera Required', 'Camera access is required for photo proof.');
      return;
    }

    const result = await ImagePicker.launchCameraAsync({
      mediaTypes: ImagePicker.MediaTypeOptions.Images,
      quality: 0.75,
      allowsEditing: false,
    });
    if (result.canceled) return;

    const uri = result.assets[0]?.uri;
    if (!uri) return;

    if (photoMap && onPhotoMapChange && side) {
      onPhotoMapChange({ ...photoMap, [side]: uri });
    } else if (onImagesChange) {
      if (images.length >= maxImages) {
        Alert.alert('Limit Reached', `You can only upload up to ${maxImages} images.`);
        return;
      }
      onImagesChange([...images, uri]);
    }
  };

  if (photoMap && onPhotoMapChange) {
    return (
      <View style={styles.container}>
        <Text style={styles.title}>{title}</Text>
        <View style={styles.grid}>
          {requiredSides.map((side) => (
            <TouchableOpacity key={side} style={styles.slot} onPress={() => takePhoto(side)} activeOpacity={0.85}>
              {photoMap[side] ? <Image source={{ uri: photoMap[side] }} style={styles.image} /> : <Text style={styles.addText}>{side.toUpperCase()}</Text>}
              <Text style={styles.slotLabel}>{photoMap[side] ? 'Retake' : 'Take Photo'}</Text>
            </TouchableOpacity>
          ))}
        </View>
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <Text style={styles.title}>{title}</Text>
      <View style={styles.grid}>
        {images.map((uri, index) => (
          <View key={`${uri}-${index}`} style={styles.slot}>
            <Image source={{ uri }} style={styles.image} />
          </View>
        ))}
        {images.length < maxImages && (
          <TouchableOpacity style={styles.slot} onPress={() => takePhoto()} activeOpacity={0.85}>
            <Text style={styles.addText}>ADD</Text>
          </TouchableOpacity>
        )}
      </View>
    </View>
  );
};

const styles = StyleSheet.create({
  container: { marginVertical: 12 },
  title: { fontSize: 15, fontWeight: '700', marginBottom: 10, color: '#1F2937' },
  grid: { flexDirection: 'row', flexWrap: 'wrap', gap: 10 },
  slot: {
    width: '48%',
    aspectRatio: 1.15,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#CBD5E1',
    backgroundColor: '#F8FAFC',
    overflow: 'hidden',
    alignItems: 'center',
    justifyContent: 'center',
  },
  image: { position: 'absolute', top: 0, right: 0, bottom: 0, left: 0, width: '100%', height: '100%' },
  addText: { fontSize: 15, fontWeight: '800', color: '#334155', textAlign: 'center' },
  slotLabel: {
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
    paddingVertical: 7,
    paddingHorizontal: 4,
    textAlign: 'center',
    backgroundColor: 'rgba(15, 23, 42, 0.72)',
    color: '#FFFFFF',
    fontSize: 11,
    fontWeight: '700',
    overflow: 'hidden',
  },
});
