export interface MoneyInfo {
  raw: number;
  formatted: string;
  formatted_minus: string;
  type: 'positive' | 'negative' | 'nil';
}