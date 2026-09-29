<template>
  <div class="po-field">
    <label :for="editable ? fieldId : undefined">{{ label }}</label>
    <i aria-hidden="true">:</i>
    <select v-if="editable && options" :id="fieldId" :value="modelValue ?? ''" @change="$emit('update:modelValue', $event.target.value)">
      <option value="">Select {{ label.toLowerCase() }}</option>
      <option v-if="modelValue && !options.includes(modelValue)" :value="modelValue">{{ modelValue }}</option>
      <option v-for="option in options" :key="option" :value="option">{{ option }}</option>
    </select>
    <textarea v-else-if="editable && multiline" :id="fieldId" :value="modelValue" rows="3" @input="$emit('update:modelValue', $event.target.value)" />
    <input v-else-if="editable" :id="fieldId" :value="modelValue" :type="type" :step="type === 'number' ? '0.01' : undefined" :min="type === 'number' ? 0 : undefined" @input="$emit('update:modelValue', $event.target.value)">
    <b v-else>{{ displayValue ?? (modelValue || '-') }}</b>
  </div>
</template>

<script setup>
import { useId } from 'vue'

defineProps({ label: String, modelValue: [String, Number], displayValue: String, editable: Boolean, multiline: Boolean, options: Array, type: { type: String, default: 'text' } })
defineEmits(['update:modelValue'])
const fieldId = useId()
</script>
