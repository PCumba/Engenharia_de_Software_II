import { Component, Inject } from '@angular/core';
import { MatDialogRef, MAT_DIALOG_DATA } from '@angular/material/dialog';

export interface ConfirmationDialogData {
  title: string;
  message: string;
  confirmText?: string;
  cancelText?: string;
  type?: 'danger' | 'warning' | 'info';
}

@Component({
  selector: 'app-confirmation-dialog',
  template: `
    <h2 mat-dialog-title class="dialog-title" [ngClass]="data.type || 'info'">
      <mat-icon>{{ getIcon() }}</mat-icon>
      {{ data.title }}
    </h2>

    <mat-dialog-content>
      <p>{{ data.message }}</p>
    </mat-dialog-content>

    <mat-dialog-actions align="end">
      <button mat-button (click)="onCancel()">
        {{ data.cancelText || 'Cancelar' }}
      </button>
      <button
        mat-raised-button
        [color]="data.type === 'danger' ? 'warn' : 'primary'"
        (click)="onConfirm()"
      >
        {{ data.confirmText || 'Confirmar' }}
      </button>
    </mat-dialog-actions>
  `,
  styles: [`
    .dialog-title {
      display: flex;
      align-items: center;
      gap: 0.5rem;

      &.danger { color: #f44336; }
      &.warning { color: #ff9800; }
      &.info { color: #1976d2; }
    }

    mat-dialog-content p {
      margin: 0;
      color: var(--text-secondary);
      line-height: 1.6;
    }

    mat-dialog-actions {
      padding: 1rem 0 0;
      gap: 0.5rem;
    }
  `]
})
export class ConfirmationDialogComponent {
  constructor(
    public dialogRef: MatDialogRef<ConfirmationDialogComponent>,
    @Inject(MAT_DIALOG_DATA) public data: ConfirmationDialogData
  ) {}

  getIcon(): string {
    const icons: { [key: string]: string } = {
      danger: 'warning',
      warning: 'info',
      info: 'help_outline'
    };
    return icons[this.data.type || 'info'];
  }

  onConfirm(): void {
    this.dialogRef.close(true);
  }

  onCancel(): void {
    this.dialogRef.close(false);
  }
}