import React from 'react';
import { View, Text, StyleSheet, TouchableOpacity, Image } from 'react-native';
import * as ImagePicker from 'expo-image-picker';

interface ImageUploadBoxProps {
  title: string;
  images: string[];
  onImagesChange: (images: string[]) => void;
  maxImages?: number;
}

export const ImageUploadBox: React.FC<ImageUploadBoxProps> = ({ title, images, onImagesChange, maxImages = 5 }) => {
  const handlePickImage = async () => {
    if (images.length >= maxImages) {
      alert(`You can only upload up to ${maxImages} images.`);
      return;
    }
    const result = await ImagePicker.launchImageLibraryAsync({
      mediaTypes: ImagePicker.MediaTypeOptions.Images,
      allowsMultipleSelection: true,
      selectionLimit: maxImages - images.length,
      quality: 0.8,
    });

    if (!result.canceled) {
      const newUris = result.assets.map(asset => asset.uri);
      onImagesChange([...images, ...newUris]);
    }
  };

  const removeImage = (index: number) => {
    const updated = [...images];
    updated.splice(index, 1);
    onImagesChange(updated);
  };

  return (
    <View style={styles.container}>
      <Text style={styles.title}>{title}</Text>
      <View style={styles.imageGrid}>
        {images.map((uri, index) => (
          <View key={index} style={styles.imageContainer}>
            <Image source={{ uri }} style={styles.image} />
            <TouchableOpacity style={styles.removeBtn} onPress={() => removeImage(index)}>
              <Text style={styles.removeText}>X</Text>
            </TouchableOpacity>
          </View>
        ))}
        {images.length < maxImages && (
          <TouchableOpacity style={styles.addBtn} onPress={handlePickImage}>
            <Text style={styles.addText}>+ Add</Text>
          </TouchableOpacity>
        )}
      </View>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    marginVertical: 12,
  },
  title: {
    fontSize: 14,
    fontWeight: '500',
    marginBottom: 8,
    color: '#333',
  },
  imageGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 10,
  },
  imageContainer: {
    position: 'relative',
    width: 80,
    height: 80,
    borderRadius: 8,
    overflow: 'hidden',
  },
  image: {
    width: '100%',
    height: '100%',
  },
  removeBtn: {
    position: 'absolute',
    top: 4,
    right: 4,
    backgroundColor: 'rgba(0,0,0,0.6)',
    borderRadius: 10,
    width: 20,
    height: 20,
    justifyContent: 'center',
    alignItems: 'center',
  },
  removeText: {
    color: '#FFF',
    fontSize: 10,
    fontWeight: 'bold',
  },
  addBtn: {
    width: 80,
    height: 80,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#CCC',
    borderStyle: 'dashed',
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#F9F9F9',
  },
  addText: {
    color: '#666',
    fontSize: 14,
  },
});
