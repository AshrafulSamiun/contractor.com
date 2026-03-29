const ENFORCE_PERMISSIONS = String(import.meta.env.VITE_ENFORCE_PERMISSIONS ?? 'true').toLowerCase() === 'true'

export const hasUserPermission = (user, module, action = 'read') => {
  if (!ENFORCE_PERMISSIONS) return true
  const matrix = user?.permissions || {}
  return Boolean(matrix?.[module]?.[action])
}
