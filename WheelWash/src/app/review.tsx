import React, { useState } from 'react';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, TextInput, KeyboardAvoidingView, Platform, StatusBar, Alert, ActivityIndicator } from 'react-native';
import { SafeAreaView, useSafeAreaInsets } from 'react-native-safe-area-context';
import { useLocalSearchParams, useRouter } from 'expo-router';
import { Brand, Spacing, Radius, Typography, Shadow } from '@/constants/theme';
import { createReview } from '@/api/reviewApi';
import { STORAGE_KEYS } from '@/lib/wheelwash-data';

const FEEDBACK_CHIPS = ['On time', 'Professional', 'Clean service', 'Value for money', 'Polite', 'Quick'];

export default function ReviewScreen() {
  const router = useRouter();
  const { bookingId } = useLocalSearchParams<{ bookingId?: string }>();
  const insets = useSafeAreaInsets();
  const [rating, setRating] = useState(5);
  const [selectedChips, setSelectedChips] = useState<string[]>(['On time', 'Clean service']);
  const [comment, setComment] = useState('');
  const [loading, setLoading] = useState(false);

  const toggleChip = (chip: string) => {
    setSelectedChips((prev) => 
      prev.includes(chip) ? prev.filter(c => c !== chip) : [...prev, chip]
    );
  };

  const submit = async () => {
    const id = bookingId || await AsyncStorage.getItem(STORAGE_KEYS.bookingId);
    if (!id) {
      Alert.alert('Booking missing', 'Please open this review from a completed booking.');
      return;
    }
    setLoading(true);
    try {
      const result = await createReview(id, rating, comment || selectedChips.join(', '));
      const reviewId = (result as any)?.id || (result as any)?.review?.id;
      if (reviewId) await AsyncStorage.setItem(STORAGE_KEYS.reviewId, String(reviewId));
      router.push('/(tabs)/bookings');
    } catch (err) {
      Alert.alert('Review failed', err instanceof Error ? err.message : 'Please try again.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <View style={styles.root}>
      <StatusBar barStyle="dark-content" backgroundColor={Brand.white} />
      
      {/* ── Top Bar ── */}
      <SafeAreaView edges={['top']} style={{ backgroundColor: Brand.white }}>
        <View style={styles.topBar}>
          <TouchableOpacity onPress={() => router.canGoBack() ? router.back() : router.replace('/')} style={styles.backBtn}>
            <Text style={styles.backIcon}>←</Text>
          </TouchableOpacity>
          <Text style={styles.headerTitle}>Rating & Review</Text>
          <View style={{ width: 40 }} />
        </View>
      </SafeAreaView>

      <KeyboardAvoidingView 
        style={{ flex: 1 }} 
        behavior={Platform.OS === 'ios' ? 'padding' : undefined}
      >
        <ScrollView style={styles.scroll} showsVerticalScrollIndicator={false} bounces={false}>
          
          {/* ── Service Info ── */}
          <View style={styles.serviceSection}>
            <Text style={styles.serviceTitle}>Premium Wash</Text>
            <View style={styles.partnerRow}>
              <View style={styles.partnerAvatar}>
                <Text style={{ fontSize: 20 }}>👨🏽‍🔧</Text>
              </View>
              <Text style={styles.partnerName}>Service by Ravi Kumar</Text>
            </View>
          </View>

          {/* ── Rating ── */}
          <View style={styles.ratingSection}>
            <Text style={styles.ratingLabel}>How was your experience?</Text>
            <View style={styles.starsRow}>
              {[1, 2, 3, 4, 5].map((star) => (
                <TouchableOpacity key={star} onPress={() => setRating(star)} activeOpacity={0.8}>
                  <Text style={[styles.starIcon, rating >= star ? styles.starFilled : styles.starEmpty]}>
                    ★
                  </Text>
                </TouchableOpacity>
              ))}
            </View>
          </View>

          {/* ── Quick Feedback Chips ── */}
          <View style={styles.section}>
            <Text style={styles.sectionTitle}>What went well?</Text>
            <View style={styles.chipsWrap}>
              {FEEDBACK_CHIPS.map((chip) => {
                const isActive = selectedChips.includes(chip);
                return (
                  <TouchableOpacity
                    key={chip}
                    style={[styles.chip, isActive && styles.chipActive]}
                    onPress={() => toggleChip(chip)}
                  >
                    <Text style={[styles.chipText, isActive && styles.chipTextActive]}>{chip}</Text>
                  </TouchableOpacity>
                );
              })}
            </View>
          </View>

          {/* ── Review Text Area ── */}
          <View style={styles.section}>
            <Text style={styles.sectionTitle}>Write a review</Text>
            <TextInput
              style={styles.textArea}
              placeholder="Tell us more about your experience..."
              placeholderTextColor={Brand.textMuted}
              multiline
              textAlignVertical="top"
              value={comment}
              onChangeText={setComment}
            />
          </View>

          {/* ── Before & After Preview ── */}
          <View style={styles.section}>
            <Text style={styles.sectionTitle}>Service Photos</Text>
            <View style={styles.galleryRow}>
              <View style={styles.galleryCard}>
                <View style={[styles.galleryImagePlaceholder, { backgroundColor: Brand.border }]}><Text style={{ fontSize: 24 }}>🚙</Text></View>
                <Text style={styles.galleryLabel}>Before</Text>
              </View>
              <View style={styles.galleryCard}>
                <View style={[styles.galleryImagePlaceholder, { backgroundColor: Brand.aquaLight }]}><Text style={{ fontSize: 24 }}>✨🚗✨</Text></View>
                <Text style={styles.galleryLabel}>After</Text>
              </View>
            </View>
          </View>

          <View style={{ height: 120 }} />
        </ScrollView>
      </KeyboardAvoidingView>

      {/* ── Sticky Bottom Button ── */}
      <View style={[styles.bottomSticky, { paddingBottom: Math.max(insets.bottom, Spacing.lg) }]}>
        <TouchableOpacity style={styles.primaryBtn} onPress={submit} activeOpacity={0.8}>
          {loading ? <ActivityIndicator color={Brand.white} /> : <Text style={styles.primaryBtnText}>Submit Review</Text>}
        </TouchableOpacity>
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  root: {
    flex: 1,
    backgroundColor: Brand.white,
  },
  topBar: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: Spacing.md,
    paddingVertical: Spacing.sm,
    backgroundColor: Brand.white,
    borderBottomWidth: 1,
    borderBottomColor: Brand.borderLight,
  },
  backBtn: {
    width: 40,
    height: 40,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: Radius.round,
    backgroundColor: Brand.offWhite,
  },
  backIcon: {
    fontSize: 18,
    color: Brand.textPrimary,
  },
  headerTitle: {
    ...Typography.h2,
    color: Brand.textPrimary,
  },
  scroll: {
    flex: 1,
  },
  serviceSection: {
    alignItems: 'center',
    paddingTop: Spacing.xxl,
    paddingBottom: Spacing.lg,
  },
  serviceTitle: {
    ...Typography.h1,
    color: Brand.textPrimary,
    marginBottom: Spacing.sm,
  },
  partnerRow: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: Brand.offWhite,
    paddingHorizontal: Spacing.md,
    paddingVertical: Spacing.xs,
    borderRadius: Radius.round,
    gap: Spacing.sm,
  },
  partnerAvatar: {
    width: 32,
    height: 32,
    borderRadius: 16,
    backgroundColor: Brand.white,
    alignItems: 'center',
    justifyContent: 'center',
  },
  partnerName: {
    ...Typography.smallMed,
    color: Brand.textSecondary,
  },
  ratingSection: {
    alignItems: 'center',
    paddingVertical: Spacing.lg,
    borderBottomWidth: 1,
    borderBottomColor: Brand.borderLight,
    marginBottom: Spacing.lg,
  },
  ratingLabel: {
    ...Typography.body,
    color: Brand.textPrimary,
    marginBottom: Spacing.md,
  },
  starsRow: {
    flexDirection: 'row',
    gap: Spacing.sm,
  },
  starIcon: {
    fontSize: 48,
  },
  starFilled: {
    color: '#FBBF24', // Yellow
  },
  starEmpty: {
    color: Brand.border,
  },
  section: {
    paddingHorizontal: Spacing.xl,
    marginBottom: Spacing.xl,
  },
  sectionTitle: {
    ...Typography.h3,
    color: Brand.textPrimary,
    marginBottom: Spacing.md,
  },
  chipsWrap: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: Spacing.sm,
  },
  chip: {
    paddingHorizontal: Spacing.md,
    paddingVertical: Spacing.sm,
    borderRadius: Radius.round,
    borderWidth: 1,
    borderColor: Brand.border,
    backgroundColor: Brand.white,
  },
  chipActive: {
    backgroundColor: Brand.royalBlue + '15',
    borderColor: Brand.royalBlue,
  },
  chipText: {
    ...Typography.smallMed,
    color: Brand.textSecondary,
  },
  chipTextActive: {
    color: Brand.royalBlue,
    fontWeight: '700',
  },
  textArea: {
    backgroundColor: Brand.offWhite,
    borderWidth: 1,
    borderColor: Brand.border,
    borderRadius: Radius.lg,
    padding: Spacing.md,
    height: 120,
    ...Typography.body,
    color: Brand.textPrimary,
  },
  galleryRow: {
    flexDirection: 'row',
    gap: Spacing.md,
  },
  galleryCard: {
    width: 120,
    gap: Spacing.sm,
  },
  galleryImagePlaceholder: {
    height: 80,
    borderRadius: Radius.lg,
    alignItems: 'center',
    justifyContent: 'center',
  },
  galleryLabel: {
    ...Typography.smallMed,
    color: Brand.textSecondary,
    textAlign: 'center',
  },
  bottomSticky: {
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
    backgroundColor: Brand.white,
    paddingHorizontal: Spacing.xl,
    paddingTop: Spacing.md,
    borderTopWidth: 1,
    borderTopColor: Brand.borderLight,
    ...Shadow.strong,
  },
  primaryBtn: {
    backgroundColor: Brand.royalBlue,
    height: 56,
    borderRadius: Radius.round,
    alignItems: 'center',
    justifyContent: 'center',
  },
  primaryBtnText: {
    ...Typography.h3,
    color: Brand.white,
  },
});
