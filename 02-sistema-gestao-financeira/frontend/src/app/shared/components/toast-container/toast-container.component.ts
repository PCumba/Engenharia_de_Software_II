import { Component } from '@angular/core';
import { MatSnackBar } from '@angular/material/snack-bar';

@Component({
  selector: 'app-toast-container',
  template: `<div></div>`,
  styles: []
})
export class ToastContainerComponent {
  constructor(private snackBar: MatSnackBar) {}

  showSuccess(message: string, duration = 3000): void {
    this.snackBar.open(message, 'Fechar', {
      duration,
      panelClass: ['success-snackbar'],
      horizontalPosition: 'end',
      verticalPosition: 'bottom'
    });
  }

  showError(message: string, duration = 5000): void {
    this.snackBar.open(message, 'Fechar', {
      duration,
      panelClass: ['error-snackbar'],
      horizontalPosition: 'end',
      verticalPosition: 'bottom'
    });
  }

  showWarning(message: string, duration = 4000): void {
    this.snackBar.open(message, 'Fechar', {
      duration,
      panelClass: ['warn-snackbar'],
      horizontalPosition: 'end',
      verticalPosition: 'bottom'
    });
  }
}