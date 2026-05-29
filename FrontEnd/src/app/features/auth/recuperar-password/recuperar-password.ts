import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { RouterLink } from '@angular/router';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { AuthService } from '../../../shared/services/auth.service';

@Component({
  selector: 'app-recuperar-password',
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
  templateUrl: './recuperar-password.html',
  styleUrls: ['./recuperar-password.css']
})
export class RecuperarPassword {
  private readonly formBuilder = inject(FormBuilder);
  private readonly authService = inject(AuthService);

  cargando = false;
  enviado = false;
  error: string | null = null;
  mensaje: string | null = null;
  warning: string | null = null;
  resetLink: string | null = null;

  readonly form = this.formBuilder.group({
    email: ['', [Validators.required, Validators.email]]
  });

  onSubmit(): void {
    if (this.form.invalid || this.cargando) {
      this.form.markAllAsTouched();
      return;
    }

    this.cargando = true;
    this.error = null;
    this.mensaje = null;
    this.warning = null;
    this.resetLink = null;

    this.authService.requestPasswordReset({ email: this.form.value.email ?? '' }).subscribe({
      next: (response) => {
        this.cargando = false;
        this.enviado = true;
        this.mensaje = response.message || 'Revisá tu casilla para continuar con el recupero.';
        this.warning = response.warning || null;
        this.resetLink = response.reset_link || null;
        this.form.disable();
      },
      error: (err: Error) => {
        this.cargando = false;
        this.error = err.message || 'No fue posible iniciar el recupero de contraseña';
      }
    });
  }
}