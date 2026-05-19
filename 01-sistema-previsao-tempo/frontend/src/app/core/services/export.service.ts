import { Injectable } from '@angular/core';
import { jsPDF } from 'jspdf';
import { I18nService } from './i18n.service';

@Injectable({
  providedIn: 'root'
})
export class ExportService {

  constructor(private i18n: I18nService) {}

  /**
   * Export search history as CSV
   */
  exportCSV(data: any[], filename: string = 'weather_history'): void {
    if (!data || data.length === 0) return;

    const headers = ['City', 'Country', 'Temperature (°C)', 'Description', 'Humidity (%)', 'Wind (m/s)', 'Date'];
    const rows = data.map(item => {
      const w = item.weather_data || item;
      return [
        w.city || item.city || '',
        w.country || item.country || '',
        w.temperature || '',
        w.description || '',
        w.humidity || '',
        w.windSpeed || '',
        item.searched_at || item.timestamp || new Date().toISOString()
      ].join(',');
    });

    const csvContent = '\uFEFF' + headers.join(',') + '\n' + rows.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    this.downloadBlob(blob, `${filename}.csv`);
  }

  /**
   * Export search history as PDF
   */
  exportPDF(data: any[], filename: string = 'weather_history'): void {
    if (!data || data.length === 0) return;

    const doc = new jsPDF();
    const pageWidth = doc.internal.pageSize.getWidth();

    // Header
    doc.setFillColor(74, 144, 217);
    doc.rect(0, 0, pageWidth, 35, 'F');

    doc.setTextColor(255, 255, 255);
    doc.setFontSize(20);
    doc.setFont('helvetica', 'bold');
    doc.text('WeatherApp', 14, 18);

    doc.setFontSize(10);
    doc.setFont('helvetica', 'normal');
    doc.text(this.i18n.t('preferences.exportHistory'), 14, 28);

    const dateStr = new Date().toLocaleDateString(this.i18n.getCurrentLang() === 'pt' ? 'pt-PT' : 'en-US');
    doc.text(dateStr, pageWidth - 14, 28, { align: 'right' });

    // Table header
    let y = 45;
    const colWidths = [35, 20, 22, 50, 20, 20, 25];
    const headers = ['City', 'Country', 'Temp °C', 'Description', 'Hum %', 'Wind', 'Date'];

    doc.setFillColor(240, 244, 248);
    doc.rect(10, y - 5, pageWidth - 20, 10, 'F');

    doc.setTextColor(74, 85, 104);
    doc.setFontSize(7);
    doc.setFont('helvetica', 'bold');

    let x = 14;
    headers.forEach((h, i) => {
      doc.text(h, x, y);
      x += colWidths[i];
    });

    // Table rows
    y += 10;
    doc.setFont('helvetica', 'normal');
    doc.setTextColor(26, 35, 50);

    data.forEach((item, index) => {
      if (y > 270) {
        doc.addPage();
        y = 20;
      }

      const w = item.weather_data || item;
      const row = [
        (w.city || item.city || '').substring(0, 18),
        (w.country || item.country || '').substring(0, 8),
        String(w.temperature || ''),
        (w.description || '').substring(0, 28),
        String(w.humidity || ''),
        String(w.windSpeed || ''),
        (item.searched_at || '').substring(0, 10)
      ];

      if (index % 2 === 0) {
        doc.setFillColor(248, 250, 252);
        doc.rect(10, y - 4, pageWidth - 20, 8, 'F');
      }

      doc.setFontSize(7);
      x = 14;
      row.forEach((cell, i) => {
        doc.text(cell, x, y);
        x += colWidths[i];
      });

      y += 8;
    });

    // Footer
    const totalPages = doc.internal.pages.length - 1;
    for (let i = 1; i <= totalPages; i++) {
      doc.setPage(i);
      doc.setFontSize(8);
      doc.setTextColor(136, 150, 166);
      doc.text(
        `${this.i18n.t('app.title')} — Page ${i}/${totalPages}`,
        pageWidth / 2,
        doc.internal.pageSize.getHeight() - 10,
        { align: 'center' }
      );
    }

    doc.save(`${filename}.pdf`);
  }

  private downloadBlob(blob: Blob, filename: string): void {
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    a.click();
    URL.revokeObjectURL(url);
  }
}
