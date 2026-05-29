import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { ActivatedRoute, RouterLink } from '@angular/router';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { AuthService } from '../../../shared/services/auth.service';

@Component({
  selector: 'app-resetear-password',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    RouterLink,
    MatButtonModule,
    MatCardModule,
    MatFormFieldModule,
    MatIconModule,
    MatInputModule
  ],
  templateUrl: './resetear-password.html',
  styleUrls: ['./resetear-password.css']
})
export class ResetearPassword implements OnInit {
  private readonly route = inject(ActivatedRoute);
  private readonly formBuilder = inject(FormBuilder);
  private readonly authService = inject(AuthService);

  token = '';
  verificando = true;
  tokenValido = false;
  guardando = false;
  error: string | null = null;
  mensaje: string | null = null;
  ocultarClave = true;
  ocultarConfirmacion = true;

  readonly form = this.formBuilder.group({
    password: ['', [Validators.required, Validators.minLength(6)]],
    confirmPassword: ['', [Validators.required]]
  });

  ngOnInit(): void {
    this.token = this.route.snapshot.queryParamMap.get('token') ?? '';

    if (!this.token) {
      this.verificando = false;
      this.error = 'Falta el token de recuperación en el enlace.';
      return;
    }

    this.authService.validatePasswordResetToken(this.token).subscribe({
      next: () => {
        this.verificando = false;
        this.tokenValido = true;
      },
      error: (err: Error) => {
        this.verificando = false;
        this.error = err.message || 'El enlace de recuperación es inválido o expiró.';
      }
    });
  }

  onSubmit(): void {
    if (this.form.invalid || !this.contrasenasCoinciden() || this.guardando || !this.tokenValido) {
      this.form.markAllAsTouched();
      return;
    }

    this.guardando = true;
    this.error = null;

    this.authService.resetPassword({
      token: this.token,
      password: this.form.value.password ?? '',
      confirm_password: this.form.value.confirmPassword ?? ''
    }).subscribe({
      next: (response) => {
        this.guardando = false;
        this.tokenValido = false;
        this.mensaje = response.message || 'Tu contraseña fue actualizada correctamente.';
      },
      error: (err: Error) => {
        this.guardando = false;
        this.error = err.message || 'No fue posible restablecer la contraseña';
      }
    });
  }

  contrasenasCoinciden(): boolean {
    return (this.form.value.password ?? '') === (this.form.value.confirmPassword ?? '');
  }
}