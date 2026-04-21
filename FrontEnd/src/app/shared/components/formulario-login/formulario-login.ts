import { Component, EventEmitter, Output, inject, ChangeDetectorRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatIconModule } from '@angular/material/icon';
import { HttpErrorResponse } from '@angular/common/http';
import { AuthService } from '../../services/auth.service';
import { AuthUser } from '../../models/auth.model';

@Component({
  selector: 'app-formulario-login',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    MatFormFieldModule,
    MatInputModule,
    MatButtonModule,
    MatCardModule,
    MatIconModule
  ],
  templateUrl: './formulario-login.html',
  styleUrl: './formulario-login.css'
})
export class FormularioLoginComponent {
  @Output() loginSuccess = new EventEmitter<AuthUser>();
  @Output() switchToRegister = new EventEmitter<void>();
  @Output() cancel = new EventEmitter<void>();
  private readonly formBuilder = inject(FormBuilder);
  private readonly cdr = inject(ChangeDetectorRef);

  cargando = false;
  error: string | null = null;
  ocultarClave = true;

  form: FormGroup;

  constructor(
    private authService: AuthService
  ) {
    this.form = this.formBuilder.group({
      email: ['', [Validators.required, Validators.email]],
      clave: ['', [Validators.required, Validators.minLength(6)]]
    });
  }

  onSubmit(): void {
    if (this.form.invalid || this.cargando) {
      this.form.markAllAsTouched();
      return;
    }

    this.cargando = true;
    this.error = null;

    const payload = {
      email: this.form.value.email ?? '',
      clave: this.form.value.clave ?? ''
    };

    this.authService.login(payload).subscribe({
      next: (user) => {
        setTimeout(() => {
          this.cargando = false;
          this.loginSuccess.emit(user);
          this.cdr.markForCheck();
        }, 0);
      },
      error: (err: HttpErrorResponse) => {
        setTimeout(() => {
          this.cargando = false;

          if (err?.error?.pendiente_aprobacion) {
            this.error = err.error.message || 'Tu cuenta está pendiente de aprobación por el administrador. Recibirás un email cuando sea aprobada.';
          } else {
            this.error = err?.error?.message || err?.message || 'Error al iniciar sesión';
          }

          this.cdr.markForCheck();
        }, 0);
      }
    });
  }

  onGoogleLogin(): void {
    if (this.cargando) {
      return;
    }

    this.cargando = true;
    this.error = null;

    this.authService.getGoogleIdentity().subscribe({
      next: (identity) => {
        this.authService.loginWithGoogleToken(identity.idToken, identity.fotoPerfilUrl).subscribe({
          next: (user) => {
            setTimeout(() => {
              this.cargando = false;
              this.loginSuccess.emit(user);
              this.cdr.markForCheck();
            }, 0);
          },
          error: (err: HttpErrorResponse | Error) => {
            setTimeout(() => {
              const httpErr = err as HttpErrorResponse;
              const backendMessage = httpErr?.error?.message || err.message || '';
              const errorCode = (err as any)?.code || '';

              if (
                errorCode === 'GOOGLE_REQUIERE_REGISTRO' ||
                httpErr?.status === 404 ||
                backendMessage.includes('No existe una cuenta registrada con este Google')
              ) {
                this.authService.setPendingGoogleIdentity(identity);
                this.cargando = false;
                this.error = null;
                this.switchToRegister.emit();
                this.cdr.markForCheck();
                return;
              }

              this.cargando = false;
              this.error = backendMessage || 'Error al iniciar sesión con Google';
              this.cdr.markForCheck();
            }, 0);
          }
        });
      },
      error: (err: Error) => {
        setTimeout(() => {
          this.cargando = false;
          this.error = err.message || 'No fue posible autenticar con Google';
          this.cdr.markForCheck();
        }, 0);
      }
    });
  }

  irARegistro(): void {
    this.switchToRegister.emit();
  }

  cancelar(): void {
    this.cancel.emit();
  }
}
