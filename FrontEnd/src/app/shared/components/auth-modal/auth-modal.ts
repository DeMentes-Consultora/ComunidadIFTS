import { AfterViewInit, Component, Inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { MAT_DIALOG_DATA, MatDialogModule, MatDialogRef } from '@angular/material/dialog';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { FormularioLoginComponent } from '../formulario-login/formulario-login';
import { FormularioRegistroComponent } from '../formulario-registro/formulario-registro';
import { AuthUser } from '../../models/auth.model';

interface AuthModalData {
  view?: 'login' | 'register';
}

@Component({
  selector: 'app-auth-modal',
  standalone: true,
  imports: [
    CommonModule,
    RouterModule,
    MatDialogModule,
    MatButtonModule,
    MatIconModule,
    FormularioLoginComponent,
    FormularioRegistroComponent
  ],
  templateUrl: './auth-modal.html',
  styleUrl: './auth-modal.css'
})
export class AuthModalComponent {
  currentView: 'login' | 'register' = 'login';

  constructor(
    private dialogRef: MatDialogRef<AuthModalComponent>,
    @Inject(MAT_DIALOG_DATA) data: AuthModalData
  ) {
    this.currentView = data?.view === 'register' ? 'register' : 'login';
    this.updateDialogSize();
  }

  ngAfterViewInit(): void {
    this.updateDialogSize();
  }

  mostrarLogin(): void {
    this.currentView = 'login';
    this.updateDialogSize();
  }

  mostrarRegistro(): void {
    this.currentView = 'register';
    this.updateDialogSize();
  }

  onAuthSuccess(user: AuthUser): void {
    this.dialogRef.close(user);
  }

  cerrar(): void {
    this.dialogRef.close();
  }

  private updateDialogSize(): void {
    const isRegister = this.currentView === 'register';
    this.dialogRef.updateSize(isRegister ? 'min(760px, 94vw)' : 'min(420px, 92vw)');
  }
}
