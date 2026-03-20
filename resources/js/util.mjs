export function formatCurrency(value, locale = 'pt-BR', currency = 'BRL') {
  if (typeof value !== "number") {
    return value;
  }
  return new Intl.NumberFormat(locale, {
    style: 'currency',
    currency: currency
  }).format(value);
}