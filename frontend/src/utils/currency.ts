export function formatRupiah(value: number | string): string {
  const num = typeof value === 'string' ? parseInt(value, 10) : value
  if (isNaN(num)) return 'Rp 0'
  return 'Rp ' + num.toLocaleString('id-ID')
}

export function parseRupiah(value: string): number {
  return parseInt(value.replace(/[^\d]/g, ''), 10) || 0
}

export function formatCurrencyInput(value: string): string {
  const cleaned = value.replace(/[^\d]/g, '')
  if (!cleaned) return ''
  return parseFloat(cleaned).toLocaleString('id-ID')
}
