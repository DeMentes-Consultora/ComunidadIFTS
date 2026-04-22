import { CommonModule } from '@angular/common';
import { HttpClient } from '@angular/common/http';
import { ChangeDetectionStrategy, Component, OnInit, OnDestroy, inject, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import { environment } from '../../../environments/environment';
import { AuthService } from '../../shared/services/auth.service';

interface ContactoResponse {
  success: boolean;
  message?: string;
}

@Component({
  selector: 'app-contacto',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './contacto.html',
  styleUrl: './contacto.css',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class Contacto {
  private readonly http = inject(HttpClient);
  private readonly fb = inject(FormBuilder);
  private readonly authService = inject(AuthService);
  private readonly dialogRef = inject(MatDialogRef<Contacto>, { optional: true });
  private readonly dialogData = inject<{ modal?: boolean } | null>(MAT_DIALOG_DATA, { optional: true });
  private readonly endpoint = `${environment.apiUrl}/contacto.php`;
  private closeTimer: ReturnType<typeof setTimeout> | null = null;
  readonly isModal = !!this.dialogData?.modal;

  readonly enviando = signal(false);
  readonly mensajeExito = signal<string | null>(null);
  readonly mensajeError = signal<string | null>(null);

  readonly form = this.fb.group({
    nombre: this.fb.nonNullable.control('', [Validators.required, Validators.maxLength(120)]),
    email: this.fb.nonNullable.control('', [Validators.required, Validators.email, Validators.maxLength(180)]),
    asunto: this.fb.nonNullable.control('', [Validators.required, Validators.maxLength(180)]),
    mensaje: this.fb.nonNullable.control('', [Validators.required, Validators.minLength(10), Validators.maxLength(3000)]),
  });

  ngOnInit(): void {
    const currentUser = this.authService.getCurrentUser();
    if (!currentUser) {
      return;
    }

    const normalizedNombre = this.normalizeText(currentUser.nombre);
    const normalizedApellido = this.normalizeText(currentUser.apellido);
    const normalizedEmail = this.normalizeText(currentUser.email);
    const fullName = [normalizedNombre, normalizedApellido].filter(Boolean).join(' ');

    if (fullName) {
      this.form.controls.nombre.setValue(fullName);
    }

    if (normalizedEmail) {
      this.form.controls.email.setValue(normalizedEmail);
    }
  }

  enviarConsulta(): void {
    if (this.form.invalid || this.enviando()) {
      this.form.markAllAsTouched();
      return;
    }

    this.enviando.set(true);
    this.mensajeExito.set(null);
    this.mensajeError.set(null);

    const payload = {
      nombre: this.form.controls.nombre.value.trim(),
      email: this.form.controls.email.value.trim(),
      asunto: this.form.controls.asunto.value.trim(),
      mensaje: this.form.controls.mensaje.value.trim(),
    };

    this.http.post<ContactoResponse>(this.endpoint, payload).subscribe({
      next: (response) => {
        this.enviando.set(false);
        if (!response.success) {
          this.mensajeError.set(response.message || 'No se pudo enviar la consulta');
          return;
        }

        const currentNombre = this.form.controls.nombre.value.trim();
        const currentEmail = this.form.controls.email.value.trim();
        this.form.reset({ nombre: currentNombre, email: currentEmail, asunto: '', mensaje: '' });
        const successMessage = response.message || 'Consulta enviada correctamente';

        if (this.isModal) {
          this.mensajeExito.set(`${successMessage} Cerrando...`);
          this.scheduleModalClose();
          return;
        }

        this.mensajeExito.set(successMessage);
      },
      error: (err) => {
        this.enviando.set(false);
        this.mensajeError.set(err?.error?.message || 'No se pudo enviar la consulta');
      },
    });
  }

  ngOnDestroy(): void {
    if (this.closeTimer) {
      clearTimeout(this.closeTimer);
      this.closeTimer = null;
    }
  }

  cerrarModal(): void {
    this.dialogRef?.close();
  }

  private scheduleModalClose(): void {
    if (this.closeTimer) {
      clearTimeout(this.closeTimer);
    }

    this.closeTimer = setTimeout(() => {
      this.dialogRef?.close(true);
      this.closeTimer = null;
    }, 1200);
  }

  private normalizeText(value: string | null | undefined): string {
    return (value ?? '').trim().replace(/\s+/g, ' ');
  }
}
