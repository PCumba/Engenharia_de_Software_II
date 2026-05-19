import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { MatSlideToggleModule } from '@angular/material/slide-toggle';

import { SharedModule } from '../../shared/shared.module';
import { SettingsComponent } from './settings/settings.component';
import { ProfileSettingsComponent } from './profile/profile-settings.component';

const routes: Routes = [
  { path: '', component: SettingsComponent, title: 'Configurações - Sistema Financeiro' },
  { path: 'profile', component: ProfileSettingsComponent, title: 'Meu Perfil' }
];

@NgModule({
  declarations: [SettingsComponent, ProfileSettingsComponent],
  imports: [SharedModule, MatSlideToggleModule, RouterModule.forChild(routes)]
})
export class SettingsModule {}