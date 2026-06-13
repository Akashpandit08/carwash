import React, { useState } from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, TextInput, StatusBar } from 'react-native';
import { SafeAreaView, useSafeAreaInsets } from 'react-native-safe-area-context';
import { useRouter } from 'expo-router';
import { Brand, Spacing, Radius, Typography, Shadow } from '@/constants/theme';

const QUICK_HELP = [
  { id: '1', title: 'Booking issue', icon: '📅' },
  { id: '2', title: 'Payment issue', icon: '💳' },
  { id: '3', title: 'Partner issue', icon: '👨🏽‍🔧' },
  { id: '4', title: 'Refund issue', icon: '💸' },
];

const FAQS = [
  { id: 'f1', q: 'How do I cancel my booking?', a: 'You can cancel your booking from the "My Bookings" section up to 2 hours before the scheduled time without any cancellation fee.' },
  { id: 'f2', q: 'What happens if it rains?', a: 'In case of rain, our partner will reschedule the wash based on your convenience at no extra cost.' },
  { id: 'f3', q: 'How long does a Premium Wash take?', a: 'A standard Premium Wash usually takes between 45 to 60 minutes depending on your vehicle size.' },
];

export default function SupportScreen() {
  const router = useRouter();
  const insets = useSafeAreaInsets();
  const [expandedFaq, setExpandedFaq] = useState<string | null>('f1');

  return (
    <View style={styles.root}>
      <StatusBar barStyle="dark-content" backgroundColor={Brand.white} />
      
      {/* ── Top Bar ── */}
      <SafeAreaView edges={['top']} style={{ backgroundColor: Brand.white }}>
        <View style={styles.topBar}>
          <TouchableOpacity onPress={() => router.canGoBack() ? router.back() : router.replace('/')} style={styles.backBtn}>
            <Text style={styles.backIcon}>←</Text>
          </TouchableOpacity>
          <Text style={styles.headerTitle}>Help & Support</Text>
          <View style={{ width: 40 }} />
        </View>
      </SafeAreaView>

      <ScrollView style={styles.scroll} showsVerticalScrollIndicator={false} bounces={false}>
        
        {/* ── Search Bar ── */}
        <View style={styles.searchSection}>
          <View style={styles.searchWrap}>
            <Text style={styles.searchIcon}>🔍</Text>
            <TextInput 
              style={styles.searchInput} 
              placeholder="Search for help..." 
              placeholderTextColor={Brand.textMuted}
            />
          </View>
        </View>

        {/* ── Quick Help ── */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>What do you need help with?</Text>
          <View style={styles.quickGrid}>
            {QUICK_HELP.map((item) => (
              <TouchableOpacity key={item.id} style={styles.quickCard} activeOpacity={0.8}>
                <View style={styles.quickIconWrap}>
                  <Text style={styles.quickIcon}>{item.icon}</Text>
                </View>
                <Text style={styles.quickTitle}>{item.title}</Text>
              </TouchableOpacity>
            ))}
          </View>
        </View>

        {/* ── FAQs ── */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Frequently Asked Questions</Text>
          <View style={styles.faqList}>
            {FAQS.map((faq) => {
              const isExpanded = expandedFaq === faq.id;
              return (
                <View key={faq.id} style={styles.faqCard}>
                  <TouchableOpacity 
                    style={styles.faqHeader} 
                    onPress={() => setExpandedFaq(isExpanded ? null : faq.id)}
                    activeOpacity={0.7}
                  >
                    <Text style={[styles.faqQ, isExpanded && { color: Brand.royalBlue }]}>{faq.q}</Text>
                    <Text style={styles.faqArrow}>{isExpanded ? '−' : '+'}</Text>
                  </TouchableOpacity>
                  {isExpanded && (
                    <View style={styles.faqBody}>
                      <Text style={styles.faqA}>{faq.a}</Text>
                    </View>
                  )}
                </View>
              );
            })}
          </View>
        </View>

        {/* ── Contact Options ── */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Still need help?</Text>
          <View style={styles.contactList}>
            <TouchableOpacity style={styles.contactItem} activeOpacity={0.8}>
              <View style={[styles.contactIconWrap, { backgroundColor: '#25D366' + '20' }]}>
                <Text style={styles.contactIcon}>💬</Text>
              </View>
              <Text style={styles.contactText}>WhatsApp Support</Text>
              <Text style={styles.contactArrow}>→</Text>
            </TouchableOpacity>

            <TouchableOpacity style={styles.contactItem} activeOpacity={0.8}>
              <View style={[styles.contactIconWrap, { backgroundColor: Brand.royalBlue + '20' }]}>
                <Text style={styles.contactIcon}>📞</Text>
              </View>
              <Text style={styles.contactText}>Call Support</Text>
              <Text style={styles.contactArrow}>→</Text>
            </TouchableOpacity>

            <TouchableOpacity style={styles.contactItem} activeOpacity={0.8}>
              <View style={[styles.contactIconWrap, { backgroundColor: Brand.warning + '20' }]}>
                <Text style={styles.contactIcon}>✉️</Text>
              </View>
              <Text style={styles.contactText}>Email Support</Text>
              <Text style={styles.contactArrow}>→</Text>
            </TouchableOpacity>
          </View>
        </View>

        <View style={{ height: 120 }} />
      </ScrollView>

      {/* ── Sticky Bottom Button ── */}
      <View style={[styles.bottomSticky, { paddingBottom: Math.max(insets.bottom, Spacing.lg) }]}>
        <TouchableOpacity style={styles.primaryBtn} activeOpacity={0.8}>
          <Text style={styles.primaryBtnText}>Chat with Support</Text>
        </TouchableOpacity>
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  root: {
    flex: 1,
    backgroundColor: Brand.offWhite,
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
  searchSection: {
    backgroundColor: Brand.white,
    padding: Spacing.xl,
    borderBottomLeftRadius: 24,
    borderBottomRightRadius: 24,
    ...Shadow.subtle,
  },
  searchWrap: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: Brand.offWhite,
    borderWidth: 1,
    borderColor: Brand.border,
    borderRadius: Radius.lg,
    paddingHorizontal: Spacing.md,
    height: 52,
  },
  searchIcon: {
    fontSize: 18,
    marginRight: Spacing.sm,
  },
  searchInput: {
    flex: 1,
    ...Typography.body,
    color: Brand.textPrimary,
    height: '100%',
  },
  section: {
    paddingHorizontal: Spacing.xl,
    paddingTop: Spacing.xxl,
  },
  sectionTitle: {
    ...Typography.h3,
    color: Brand.textPrimary,
    marginBottom: Spacing.md,
  },
  quickGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: Spacing.md,
  },
  quickCard: {
    width: '47%',
    backgroundColor: Brand.white,
    padding: Spacing.lg,
    borderRadius: Radius.xl,
    ...Shadow.subtle,
    alignItems: 'center',
    gap: Spacing.sm,
  },
  quickIconWrap: {
    width: 48,
    height: 48,
    borderRadius: Radius.round,
    backgroundColor: Brand.surface,
    alignItems: 'center',
    justifyContent: 'center',
  },
  quickIcon: {
    fontSize: 24,
  },
  quickTitle: {
    ...Typography.smallMed,
    color: Brand.textPrimary,
    textAlign: 'center',
  },
  faqList: {
    gap: Spacing.sm,
  },
  faqCard: {
    backgroundColor: Brand.white,
    borderRadius: Radius.xl,
    overflow: 'hidden',
    ...Shadow.subtle,
  },
  faqHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    padding: Spacing.lg,
  },
  faqQ: {
    flex: 1,
    ...Typography.bodyMed,
    color: Brand.textPrimary,
    paddingRight: Spacing.md,
  },
  faqArrow: {
    fontSize: 24,
    color: Brand.textMuted,
  },
  faqBody: {
    paddingHorizontal: Spacing.lg,
    paddingBottom: Spacing.lg,
    borderTopWidth: 1,
    borderTopColor: Brand.borderLight,
    paddingTop: Spacing.md,
  },
  faqA: {
    ...Typography.body,
    color: Brand.textSecondary,
    lineHeight: 22,
  },
  contactList: {
    backgroundColor: Brand.white,
    borderRadius: Radius.xl,
    paddingHorizontal: Spacing.lg,
    ...Shadow.subtle,
  },
  contactItem: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: Spacing.lg,
    borderBottomWidth: 1,
    borderBottomColor: Brand.borderLight,
  },
  contactIconWrap: {
    width: 40,
    height: 40,
    borderRadius: Radius.round,
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: Spacing.md,
  },
  contactIcon: {
    fontSize: 18,
  },
  contactText: {
    flex: 1,
    ...Typography.bodyMed,
    color: Brand.textPrimary,
  },
  contactArrow: {
    ...Typography.h3,
    color: Brand.textMuted,
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
