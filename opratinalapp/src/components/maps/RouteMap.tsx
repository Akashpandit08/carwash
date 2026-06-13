import React from 'react';
import { LiveTrackingMap } from './LiveTrackingMap';
import { LatLng } from '../../utils/maps';

type RouteMapProps = {
  currentLocation?: LatLng;
  destination?: LatLng & { title?: string };
  height?: number;
};

export function RouteMap({ currentLocation, destination, height = 240 }: RouteMapProps) {
  return <LiveTrackingMap currentLocation={currentLocation} destination={destination} height={height} />;
}
