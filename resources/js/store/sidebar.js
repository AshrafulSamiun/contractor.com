import { ref } from 'vue'

export const sidebarOpen = ref(false)
export const sidebarHidden = ref(false)

export function toggleSidebar() {
    sidebarOpen.value = !sidebarOpen.value
}

export function closeSidebar() {
    sidebarOpen.value = false
}

export function toggleSidebarHidden() {
    sidebarHidden.value = !sidebarHidden.value
}
