import React from 'react';
import { StyleSheet, View } from 'react-native';
import { AppButton } from '../AppButton';
import { callPhone } from '../../utils/jobContact';
import { openDirections, openMapPoint } from '../../utils/maps';

type MapActionButtonsProps = {
  latitude?: number;
  longitude?: number;
  label?: string;
  phone?: string;
  onRefresh?: () => void;
};

export function MapActionButtons({ latitude, longitude, label = 'Destination', phone, onRefresh }: MapActionButtonsProps) {
  const hasLocation = latitude !== undefined && longitude !== undefined;

  return (
    <View style={styles.wrap}>
      <View style={styles.row}>
        <AppButton title="Get Directions" onPress={() => hasLocation && openDirections(latitude, longitude, label)} type="secondary" style={styles.btn} disabled={!hasLocation} />
        <AppButton title="Refresh Location" onPress={onRefresh || (() => null)} type="secondary" style={styles.btn} disabled={!onRefresh} />
      </View>
      <View style={styles.row}>
        <AppButton title="Call Customer" onPress={() => callPhone(phone)} type="secondary" style={styles.btn} disabled={!phone} />
        <AppButton title="Open in Google Maps" onPress={() => hasLocation && openMapPoint(latitude, longitude, label)} type="secondary" style={styles.btn} disabled={!hasLocation} />
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  wrap: { marginBottom: 12 },
  row: { flexDirection: 'row', gap: 10 },
  btn: { flex: 1 },
});
