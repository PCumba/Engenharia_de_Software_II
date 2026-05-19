import { Pipe, PipeTransform } from '@angular/core';

@Pipe({ name: 'currencyBrl' })
export class CurrencyBrlPipe implements PipeTransform {
  transform(value: number | null | undefined, currency = 'BRL', showSymbol = true): string {
    if (value === null || value === undefined) return '-';

    return new Intl.NumberFormat('pt-BR', {
      style: showSymbol ? 'currency' : 'decimal',
      currency,
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    }).format(value);
  }
}