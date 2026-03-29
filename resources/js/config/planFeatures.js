const PLAN_RANK = {
  basic: 1,
  standard: 2,
  enterprise: 3,
}

const ENFORCE_PLAN_FEATURES = String(import.meta.env.VITE_ENFORCE_PLAN_FEATURES ?? 'false').toLowerCase() === 'true'

export const FEATURE_MIN_PLAN = {
  profiles_core: 'standard',
  storage_management: 'standard',
  pickup_management: 'standard',
  pickup_external_locker: 'enterprise',
  locker_access_management: 'enterprise',
  user_management: 'enterprise',
  workforce_management: 'enterprise',
  reporting: 'enterprise',
  notification_center: 'enterprise',
}

const normalizePlan = (plan) => {
  const value = String(plan || '').trim().toLowerCase()
  if (PLAN_RANK[value]) return value
  return 'standard'
}

export const getRequiredPlan = (feature) => FEATURE_MIN_PLAN[feature] || 'basic'

export const hasPlanFeature = (plan, feature) => {
  if (!ENFORCE_PLAN_FEATURES) return true
  const required = getRequiredPlan(feature)
  return (PLAN_RANK[normalizePlan(plan)] || 0) >= (PLAN_RANK[normalizePlan(required)] || 0)
}

export const formatPlanLabel = (plan) => {
  const key = normalizePlan(plan)
  if (key === 'basic') return 'Basic'
  if (key === 'enterprise') return 'Enterprise'
  return 'Standard'
}
