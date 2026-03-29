export const FACILITY_TYPE_OPTIONS = [
  { id: 1, value: 'clinics', label: 'Clinics' },
  { id: 2, value: 'colleges', label: 'Colleges' },
  { id: 3, value: 'commercial_building', label: 'Commercial Building' },
  { id: 4, value: 'condominiums', label: 'Condominiums' },
  { id: 5, value: 'corporate_offices', label: 'Corporate Offices' },
  { id: 6, value: 'co_working_spaces', label: 'Co-working Spaces' },
  { id: 7, value: 'government_offices', label: 'Government Offices' },
  { id: 8, value: 'hospitals', label: 'Hospitals' },
  { id: 9, value: 'hostels', label: 'Hostels' },
  { id: 10, value: 'hotels', label: 'Hotels' },
  { id: 11, value: 'municipal_buildings', label: 'Municipal Buildings' },
  { id: 12, value: 'nursing_homes', label: 'Nursing Homes' },
  { id: 13, value: 'office_buildings', label: 'Office Buildings' },
  { id: 14, value: 'police_stations', label: 'Police Stations' },
  { id: 15, value: 'property_management', label: 'Property Management' },
  { id: 16, value: 'public_libraries', label: 'Public Libraries' },
  { id: 17, value: 'residential_buildings', label: 'Residential Buildings' },
  { id: 18, value: 'schools', label: 'Schools' },
  { id: 19, value: 'senior_living_communities', label: 'Senior Living Communities' },
  { id: 20, value: 'serviced_apartments', label: 'Serviced Apartments' },
  { id: 21, value: 'shopping_malls', label: 'Shopping Malls' },
  { id: 22, value: 'universities', label: 'Universities' },
]

export const FACILITY_TYPE_LABELS = FACILITY_TYPE_OPTIONS.map((item) => item.label)

export const FACILITY_TYPE_BY_ID = FACILITY_TYPE_OPTIONS.reduce((acc, item) => {
  acc[String(item.id)] = item
  return acc
}, {})

export const FACILITY_TYPE_BY_LABEL = FACILITY_TYPE_OPTIONS.reduce((acc, item) => {
  acc[item.label] = item
  return acc
}, {})
