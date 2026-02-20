import { Component, EventEmitter, Output, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatIconModule } from '@angular/material/icon';
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
        this.cargando = false;
        this.loginSuccess.emit(user);
      },
      error: (err: Error) => {
        this.cargando = false;
        this.error = err.message;
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
