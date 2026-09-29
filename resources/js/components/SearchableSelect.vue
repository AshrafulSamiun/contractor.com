<template>
  <div ref="root" class="search-select">
    <div class="search-select-control">
      <input
        ref="input"
        v-model="query"
        :aria-label="ariaLabel"
        :aria-expanded="open"
        :aria-controls="listId"
        aria-autocomplete="list"
        autocomplete="off"
        role="combobox"
        :placeholder="placeholder"
        @focus="show"
        @click="show"
        @input="onInput"
        @keydown="onKeydown"
        @blur="onBlur"
      >
      <button type="button" tabindex="-1" :aria-label="`Open ${ariaLabel} options`" @mousedown.prevent="toggle">
        <ChevronDown aria-hidden="true" />
      </button>
    </div>
    <Teleport to="body">
      <div v-if="open" :id="listId" class="search-select-menu" role="listbox" :style="menuStyle" @mousedown.prevent>
        <button
          v-for="(option, index) in filteredOptions"
          :key="optionKey(option)"
          type="button"
          role="option"
          :aria-selected="isSelected(option)"
          :class="{ active: index === activeIndex, selected: isSelected(option) }"
          @mouseenter="activeIndex = index"
          @click="choose(option)"
        >
          <strong>{{ labelOf(option) }}</strong>
          <small v-if="descriptionOf(option)">{{ descriptionOf(option) }}</small>
        </button>
        <p v-if="!filteredOptions.length">No matching options</p>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, ref, useId, watch } from 'vue'
import { ChevronDown } from 'lucide-vue-next'

const props = defineProps({
  modelValue: { type: [String, Number], default: null },
  options: { type: Array, default: () => [] },
  optionLabel: { type: String, default: 'label' },
  optionValue: { type: String, default: 'value' },
  descriptionKeys: { type: Array, default: () => [] },
  placeholder: { type: String, default: 'Search...' },
  ariaLabel: { type: String, required: true },
})
const emit = defineEmits(['update:modelValue', 'select'])
const root = ref(null), input = ref(null), query = ref(''), open = ref(false), activeIndex = ref(0), menuStyle = ref({})
const listId = `search-select-${useId()}`
const labelOf = option => String(option?.[props.optionLabel] ?? '')
const valueOf = option => option?.[props.optionValue]
const descriptionOf = option => props.descriptionKeys.map(key => option?.[key]).filter(Boolean).join(' - ')
const optionKey = option => String(valueOf(option) ?? labelOf(option))
const selectedOption = computed(() => props.options.find(option => String(valueOf(option)) === String(props.modelValue ?? '')))
const filteredOptions = computed(() => {
  const term = query.value.trim().toLocaleLowerCase()
  if (!term || labelOf(selectedOption.value).toLocaleLowerCase() === term) return props.options
  return props.options.filter(option => `${labelOf(option)} ${descriptionOf(option)}`.toLocaleLowerCase().includes(term))
})
const isSelected = option => String(valueOf(option)) === String(props.modelValue ?? '')

function positionMenu() {
  const rect = root.value?.getBoundingClientRect()
  if (!rect) return
  menuStyle.value = { left: `${rect.left}px`, top: `${rect.bottom + 4}px`, width: `${rect.width}px` }
}
function show() { open.value = true; activeIndex.value = Math.max(0, filteredOptions.value.findIndex(isSelected)); nextTick(positionMenu) }
function toggle() { open.value ? close() : (input.value?.focus(), show()) }
function close() { open.value = false }
function choose(option, restoreFocus = true) {
  query.value = labelOf(option)
  emit('update:modelValue', valueOf(option))
  emit('select', option)
  close()
  if (restoreFocus) input.value?.focus()
}
function onInput() { emit('update:modelValue', null); activeIndex.value = 0; show() }
function onBlur() {
  const exact = props.options.find(option => labelOf(option).toLocaleLowerCase() === query.value.trim().toLocaleLowerCase())
  if (exact) choose(exact, false)
  else { query.value = labelOf(selectedOption.value); close() }
}
function onKeydown(event) {
  if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
    event.preventDefault(); show()
    const direction = event.key === 'ArrowDown' ? 1 : -1
    activeIndex.value = (activeIndex.value + direction + filteredOptions.value.length) % Math.max(filteredOptions.value.length, 1)
  } else if (event.key === 'Enter' && open.value && filteredOptions.value[activeIndex.value]) {
    event.preventDefault(); choose(filteredOptions.value[activeIndex.value])
  } else if (event.key === 'Escape') close()
}
function onViewportChange() { if (open.value) positionMenu() }
watch([() => props.modelValue, () => props.options], () => { query.value = labelOf(selectedOption.value) }, { immediate: true })
window.addEventListener('resize', onViewportChange)
window.addEventListener('scroll', onViewportChange, true)
onBeforeUnmount(() => { window.removeEventListener('resize', onViewportChange); window.removeEventListener('scroll', onViewportChange, true) })
</script>
