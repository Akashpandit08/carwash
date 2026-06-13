import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, FlatList, RefreshControl, Modal, TextInput, TouchableOpacity, Alert, Switch, ScrollView } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { LoadingView } from './LoadingView';
import { EmptyState } from './EmptyState';
import { AppButton } from './AppButton';

export interface CrudField {
  name: string;
  label: string;
  type: 'text' | 'number' | 'switch' | 'select' | 'multi-select';
  options?: { label: string; value: string | number }[]; // for select/multi-select
  required?: boolean;
}

interface GenericCrudScreenProps {
  title: string;
  fields: CrudField[];
  fetchApi: () => Promise<any>;
  createApi?: (data: any) => Promise<any>;
  updateApi?: (id: string | number, data: any) => Promise<any>;
  deleteApi?: (id: string | number) => Promise<any>;
  renderCard: (item: any) => React.ReactNode;
}

export const GenericCrudScreen: React.FC<GenericCrudScreenProps> = ({
  title,
  fields,
  fetchApi,
  createApi,
  updateApi,
  deleteApi,
  renderCard
}) => {
  const [data, setData] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  // Modal State
  const [modalVisible, setModalVisible] = useState(false);
  const [editingItem, setEditingItem] = useState<any>(null);
  const [formData, setFormData] = useState<any>({});
  const [saving, setSaving] = useState(false);

  const loadData = async () => {
    try {
      const res = await fetchApi();
      const payload = res.data?.data || res.data || [];
      setData(Array.isArray(payload) ? payload : payload.data || []);
    } catch (e) {
      console.log(`Failed to fetch ${title}`, e);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useEffect(() => {
    loadData();
  }, []);

  const onRefresh = () => {
    setRefreshing(true);
    loadData();
  };

  const openAddModal = () => {
    const initData: any = {};
    fields.forEach(f => {
      if (f.type === 'switch') initData[f.name] = false;
      else if (f.type === 'multi-select') initData[f.name] = [];
      else initData[f.name] = '';
    });
    setEditingItem(null);
    setFormData(initData);
    setModalVisible(true);
  };

  const openEditModal = (item: any) => {
    setEditingItem(item);
    // Pre-fill form data based on existing item
    const prefill: any = {};
    fields.forEach(f => {
      let val = item[f.name];
      if (f.type === 'number') {
        val = val !== undefined && val !== null ? String(val) : '';
      } else if (f.type === 'multi-select') {
        val = Array.isArray(val) ? val : [];
      }
      prefill[f.name] = val;
    });
    setFormData(prefill);
    setModalVisible(true);
  };

  const handleDelete = (item: any) => {
    Alert.alert('Delete', `Are you sure you want to delete this ${title.toLowerCase()}?`, [
      { text: 'Cancel', style: 'cancel' },
      { 
        text: 'Delete', 
        style: 'destructive',
        onPress: async () => {
          if (!deleteApi) return;
          try {
            await deleteApi(item.id);
            loadData();
          } catch (e: any) {
            Alert.alert('Error', e.response?.data?.message || 'Failed to delete');
          }
        }
      }
    ]);
  };

  const handleSave = async () => {
    // Basic validation
    for (const f of fields) {
      if (f.required && (formData[f.name] === '' || formData[f.name] === null || formData[f.name] === undefined)) {
        Alert.alert('Validation Error', `${f.label} is required`);
        return;
      }
    }

    setSaving(true);
    try {
      // Cast payload types
      const payload = { ...formData };
      fields.forEach(f => {
        if (f.type === 'number' && payload[f.name] !== '') {
          payload[f.name] = Number(payload[f.name]);
        }
      });

      if (editingItem && updateApi) {
        await updateApi(editingItem.id, payload);
      } else if (!editingItem && createApi) {
        await createApi(payload);
      }
      
      setModalVisible(false);
      loadData();
    } catch (e: any) {
      let errorMsg = e.response?.data?.message || 'Failed to save';
      if (e.response?.data?.errors) {
        const errors = e.response.data.errors;
        const firstErrorKey = Object.keys(errors)[0];
        if (firstErrorKey && errors[firstErrorKey].length > 0) {
          errorMsg = errors[firstErrorKey][0];
        }
      }
      Alert.alert('Error', errorMsg);
    } finally {
      setSaving(false);
    }
  };

  const updateField = (name: string, value: any) => {
    setFormData((prev: any) => ({ ...prev, [name]: value }));
  };

  if (loading) return <LoadingView message={`Loading ${title}...`} />;

  return (
    <View style={styles.container}>
      <FlatList
        data={data}
        keyExtractor={(item, idx) => item.id?.toString() || idx.toString()}
        renderItem={({ item }) => (
          <View style={styles.cardContainer}>
            {renderCard(item)}
            {(updateApi || deleteApi) && (
              <View style={styles.actionsRow}>
                {updateApi && (
                  <TouchableOpacity style={styles.actionBtn} onPress={() => openEditModal(item)}>
                    <Ionicons name="create-outline" size={20} color="#007BFF" />
                    <Text style={styles.editText}>Edit</Text>
                  </TouchableOpacity>
                )}
                {deleteApi && (
                  <TouchableOpacity style={[styles.actionBtn, { borderLeftWidth: 1, borderColor: '#EEE' }]} onPress={() => handleDelete(item)}>
                    <Ionicons name="trash-outline" size={20} color="#FF3B30" />
                    <Text style={styles.deleteText}>Delete</Text>
                  </TouchableOpacity>
                )}
              </View>
            )}
          </View>
        )}
        ListEmptyComponent={<EmptyState title={`No ${title} Found`} />}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />}
        contentContainerStyle={data.length === 0 ? { flex: 1 } : { paddingBottom: 100 }}
      />

      {createApi && (
        <TouchableOpacity style={styles.fab} onPress={openAddModal}>
          <Ionicons name="add" size={28} color="#FFF" />
        </TouchableOpacity>
      )}

      <Modal visible={modalVisible} transparent animationType="slide">
        <View style={styles.modalOverlay}>
          <View style={styles.modalContent}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>{editingItem ? 'Edit' : 'Add'} {title}</Text>
              <TouchableOpacity onPress={() => setModalVisible(false)}>
                <Ionicons name="close" size={24} color="#333" />
              </TouchableOpacity>
            </View>
            
            <ScrollView style={styles.formScroll}>
              {fields.map((f, i) => (
                <View key={i} style={styles.fieldContainer}>
                  <Text style={styles.label}>{f.label} {f.required && '*'}</Text>
                  
                  {f.type === 'text' || f.type === 'number' ? (
                    <TextInput
                      style={styles.input}
                      value={formData[f.name] !== undefined && formData[f.name] !== null ? String(formData[f.name]) : ''}
                      onChangeText={(val) => updateField(f.name, val)}
                      keyboardType={f.type === 'number' ? 'numeric' : 'default'}
                      placeholder={`Enter ${f.label.toLowerCase()}`}
                    />
                  ) : f.type === 'switch' ? (
                    <Switch
                      value={!!formData[f.name]}
                      onValueChange={(val) => updateField(f.name, val)}
                    />
                  ) : f.type === 'select' && f.options ? (
                    <View style={styles.selectContainer}>
                      {f.options.map((opt, oIdx) => (
                        <TouchableOpacity
                          key={oIdx}
                          style={[styles.optionBtn, formData[f.name] === opt.value && styles.optionBtnActive]}
                          onPress={() => updateField(f.name, opt.value)}
                        >
                          <Text style={[styles.optionText, formData[f.name] === opt.value && styles.optionTextActive]}>{opt.label}</Text>
                        </TouchableOpacity>
                      ))}
                    </View>
                  ) : f.type === 'multi-select' && f.options ? (
                    <View style={styles.selectContainer}>
                      {f.options.map((opt, oIdx) => {
                        const isSelected = Array.isArray(formData[f.name]) && formData[f.name].includes(opt.value);
                        return (
                          <TouchableOpacity
                            key={oIdx}
                            style={[styles.optionBtn, isSelected && styles.optionBtnActive]}
                            onPress={() => {
                              const current = Array.isArray(formData[f.name]) ? formData[f.name] : [];
                              if (isSelected) {
                                updateField(f.name, current.filter((v: any) => v !== opt.value));
                              } else {
                                updateField(f.name, [...current, opt.value]);
                              }
                            }}
                          >
                            <Text style={[styles.optionText, isSelected && styles.optionTextActive]}>{opt.label}</Text>
                          </TouchableOpacity>
                        );
                      })}
                    </View>
                  ) : null}
                </View>
              ))}
            </ScrollView>

            <AppButton title={saving ? "Saving..." : "Save"} onPress={handleSave} disabled={saving} style={{ marginTop: 16 }} />
          </View>
        </View>
      </Modal>
    </View>
  );
};

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#F5F7FA' },
  cardContainer: { backgroundColor: '#FFF', marginHorizontal: 16, marginTop: 16, borderRadius: 12, elevation: 1, overflow: 'hidden' },
  actionsRow: { flexDirection: 'row', borderTopWidth: 1, borderTopColor: '#EEE', marginTop: 12 },
  actionBtn: { flex: 1, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', paddingVertical: 12, gap: 8 },
  editText: { color: '#007BFF', fontWeight: 'bold' },
  deleteText: { color: '#FF3B30', fontWeight: 'bold' },
  fab: { position: 'absolute', bottom: 24, right: 24, backgroundColor: '#007BFF', width: 60, height: 60, borderRadius: 30, alignItems: 'center', justifyContent: 'center', elevation: 4 },
  modalOverlay: { flex: 1, backgroundColor: 'rgba(0,0,0,0.5)', justifyContent: 'flex-end' },
  modalContent: { backgroundColor: '#FFF', borderTopLeftRadius: 20, borderTopRightRadius: 20, padding: 20, maxHeight: '80%' },
  modalHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 16 },
  modalTitle: { fontSize: 20, fontWeight: 'bold', color: '#333' },
  formScroll: { flexGrow: 0 },
  fieldContainer: { marginBottom: 16 },
  label: { fontSize: 14, fontWeight: 'bold', color: '#555', marginBottom: 8 },
  input: { borderWidth: 1, borderColor: '#DDD', borderRadius: 8, paddingHorizontal: 12, height: 48, fontSize: 16, backgroundColor: '#F9F9F9' },
  selectContainer: { flexDirection: 'row', flexWrap: 'wrap', gap: 8 },
  optionBtn: { borderWidth: 1, borderColor: '#DDD', paddingHorizontal: 16, paddingVertical: 10, borderRadius: 8, backgroundColor: '#F9F9F9' },
  optionBtnActive: { backgroundColor: '#007BFF', borderColor: '#007BFF' },
  optionText: { color: '#555', fontWeight: 'bold' },
  optionTextActive: { color: '#FFF' },
});
