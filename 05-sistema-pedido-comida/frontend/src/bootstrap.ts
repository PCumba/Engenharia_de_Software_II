/**
 * Sistema de Pedido de Comida
 * Version 1.0.0
 * 
 * Full-stack food delivery system with:
 * - Restaurant listing and filtering
 * - Menu browsing with categories
 * - Shopping cart and checkout
 * - Order tracking and history
 * - Review and rating system
 */

import { bootstrapApplication } from '@angular/platform-browser';
import { provideRouter } from '@angular/router';
import { provideHttpClient, withInterceptors } from '@angular/common/http';
import { AppComponent } from './app/app.component';

// Note: This file is a template for modern Angular standalone API
// The current implementation uses NgModule approach for broader compatibility
