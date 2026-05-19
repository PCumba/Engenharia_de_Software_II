import { Injectable } from '@angular/core';
import { I18nService } from '../../../core/services/i18n.service';

@Injectable()
export class ExportService {
  constructor(private i18n: I18nService) {}

  /**
   * Exportar dados como CSV
   */
  exportCSV(data: any[], filename: string): void {
    if (!data || data.length === 0) return;

    const headers = Object.keys(data[0]);
    const csvRows = [
      headers.join(','),
      ...data.map(row =>
        headers.map(h => {
          const val = row[h] ?? '';
          return typeof val === 'string' && val.includes(',') ? `"${val}"` : val;
        }).join(',')
      )
    ];

    const blob = new Blob(['\uFEFF' + csvRows.join('\n')], { type: 'text/csv;charset=utf-8;' });
    this.downloadBlob(blob, `${filename}.csv`);
  }

  /**
   * Exportar dados como PDF (usando jsPDF)
   */
  async exportPDF(data: any[], title: string, filename: string): Promise<void> {
    // Dynamic import to keep bundle small
    const { default: jsPDF } = await import('jspdf');
    const doc = new jsPDF();

    // Header
    doc.setFontSize(18);
    doc.setTextColor(63, 81, 181);
    doc.text(title, 14, 22);

    doc.setFontSize(10);
    doc.setTextColor(100);
    doc.text(`${this.i18n.t('reports.export')} - ${new Date().toLocaleDateString()}`, 14, 30);

    // Separator line
    doc.setDrawColor(63, 81, 181);
    doc.setLineWidth(0.5);
    doc.line(14, 33, 196, 33);

    if (!data || data.length === 0) {
      doc.setFontSize(12);
      doc.text(this.i18n.t('common.noData'), 14, 45);
      doc.save(`${filename}.pdf`);
      return;
    }

    // Table headers
    const headers = Object.keys(data[0]);
    const colWidth = (196 - 14) / headers.length;
    let y = 40;

    doc.setFontSize(9);
    doc.setFont('helvetica', 'bold');
    doc.setFillColor(63, 81, 181);
    doc.rect(14, y, 182, 8, 'F');
    doc.setTextColor(255, 255, 255);
    headers.forEach((h, i) => doc.text(h, 14 + i * colWidth + 2, y + 5.5));

    // Table rows
    doc.setFont('helvetica', 'normal');
    doc.setTextColor(0);
    y += 8;

    data.forEach((row, idx) => {
      if (y > 270) {
        doc.addPage();
        y = 20;
      }

      if (idx % 2 === 0) {
        doc.setFillColor(245, 245, 245);
        doc.rect(14, y, 182, 7, 'F');
      }

      headers.forEach((h, i) => {
        const val = String(row[h] ?? '').substring(0, 25);
        doc.text(val, 14 + i * colWidth + 2, y + 5);
      });
      y += 7;
    });

    // Footer
    const pageCount = doc.getNumberOfPages();
    for (let i = 1; i <= pageCount; i++) {
      doc.setPage(i);
      doc.setFontSize(8);
      doc.setTextColor(150);
      doc.text(`${i} / ${pageCount}`, 100, 290, { align: 'center' });
    }

    doc.save(`${filename}.pdf`);
  }

  /**
   * Download de blob
   */
  private downloadBlob(blob: Blob, filename: string): void {
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    a.click();
    window.URL.revokeObjectURL(url);
  }
}
