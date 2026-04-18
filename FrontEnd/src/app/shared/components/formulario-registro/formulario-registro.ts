import { Component, EventEmitter, OnInit, Output, ChangeDetectorRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { MatCardModule } from '@angular/material/card';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { MatSelectModule } from '@angular/material/select';
import { MatIconModule } from '@angular/material/icon';
import { MatSnackBar, MatSnackBarModule } from '@angular/material/snack-bar';
import { HttpErrorResponse } from '@angular/common/http';
import { AuthService } from '../../services/auth.service';
import { AuthUser, GoogleIdentity, GoogleRegisterRequest } from '../../models/auth.model';
import { Carrera, Institucion } from '../../models/institucion.model';
import { InstitucionesService } from '../../services/instituciones.service';

@Component({
  selector: 'app-formulario-registro',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    MatCardModule,
    MatFormFieldModule,
    MatInputModule,
    MatButtonModule,
    MatSelectModule,
    MatIconModule,
    MatSnackBarModule
  ],
  templateUrl: './formulario-registro.html',
  styleUrl: './formulario-registro.css'
})
export class FormularioRegistroComponent implements OnInit {
  @Output() registerSuccess = new EventEmitter<AuthUser>();
  @Output() switchToLogin = new EventEmitter<void>();
  @Output() cancel = new EventEmitter<void>();

  instituciones: Institucion[] = [];
  carrerasDisponibles: Carrera[] = [];
  aniosCursada = [1, 2, 3, 4, 5];
  cargando = false;
  cargandoInstituciones = false;
  error: string | null = null;
  ocultarClave = true;
  ocultarConfirmarClave = true;
  googleIdToken: string | null = null;
  googleFotoPerfilUrl: string | null = null;
  googleHelperMessage: string | null = null;

  form;

  constructor(
    private fb: FormBuilder,
    private authService: AuthService,
    private institucionesService: InstitucionesService,
    private cdr: ChangeDetectorRef,
    private snackBar: MatSnackBar
  ) {
    this.form = this.fb.group({
      nombre: ['', [Validators.required, Validators.minLength(2)]],
      apellido: ['', [Validators.required, Validators.minLength(2)]],
      dni: ['', [Validators.required, Validators.pattern(/^\d{7,9}$/)]],
      fecha_nacimiento: ['', [Validators.required]],
      telefono: ['', [Validators.required, Validators.minLength(8)]],
      id_institucion: ['', [Validators.required]],
      id_carrera: ['', [Validators.required]],
      anio_cursada: ['', [Validators.required]],
      email: ['', [Validators.required, Validators.email]],
      clave: ['', [Validators.required, Validators.minLength(6)]],
      confirmar_clave: ['', [Validators.required]]
    });
  }

  ngOnInit(): void {
    this.cargarInstituciones();
    this.precargarDesdeGooglePendiente();

    this.form.valueChanges.subscribe(() => {
      if (this.googleFlowActivo) {
        this.actualizarAyudaFlujoGoogle();
      }
    });

    this.form.get('id_institucion')?.valueChanges.subscribe((idInstitucion) => {
      this.actualizarCarrerasDisponibles(Number(idInstitucion ?? 0));
    });
  }

  onSubmit(): void {
    if (this.cargando) {
      return;
    }

    if (this.googleFlowActivo) {
      const payload = this.buildGooglePayload();
      if (!payload) {
        this.form.markAllAsTouched();
        this.actualizarAyudaFlujoGoogle();
        this.error = null;
        this.cdr.markForCheck();
        return;
      }

      this.finalizarRegistroConGoogle();
      return;
    }

    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }

    if (!this.contrasenasCoinciden()) {
      setTimeout(() => {
        this.error = 'Las contraseñas no coinciden';
        this.cdr.markForCheck();
      }, 0);
      return;
    }

    this.cargando = true;
    this.error = null;

    const payload = {
      nombre: this.form.value.nombre ?? '',
      apellido: this.form.value.apellido ?? '',
      dni: this.form.value.dni ?? '',
      fecha_nacimiento: this.form.value.fecha_nacimiento ?? '',
      telefono: this.form.value.telefono ?? '',
      id_institucion: Number(this.form.value.id_institucion ?? 0),
      id_carrera: Number(this.form.value.id_carrera ?? 0),
      anio_cursada: Number(this.form.value.anio_cursada ?? 0),
      email: this.form.value.email ?? '',
      clave: this.form.value.clave ?? '',
      confirmar_clave: this.form.value.confirmar_clave ?? ''
    };

    this.authService.register(payload).subscribe({
      next: (response: any) => {
        setTimeout(() => {
          this.cargando = false;
          this.cdr.markForCheck();
          
          // Verificar si el usuario está pendiente de aprobación
          if (response.pendiente_aprobacion) {
            this.error = null;
            this.mostrarMensaje(
              response.message || 'Registro exitoso. Tu solicitud está pendiente de aprobación por el administrador. Recibirás un email cuando sea aprobada.',
              'success'
            );
            this.cancel.emit();
          } else {
            this.mostrarMensaje('Registro exitoso. Bienvenido.', 'success');
            this.registerSuccess.emit(response.data || response);
          }
        }, 0);
      },
      error: (err: HttpErrorResponse | Error) => {
        setTimeout(() => {
          this.cargando = false;
          const backendMessage = (err as HttpErrorResponse)?.error?.message;
          this.error = backendMessage || err.message || 'Error al registrar usuario';
          this.cdr.markForCheck();
        }, 0);
      }
    });
  }

  onGoogleRegister(): void {
    if (this.cargando) {
      return;
    }

    this.cargando = true;
    this.error = null;
    this.googleHelperMessage = null;

    this.authService.getGoogleIdentity().subscribe({
      next: (identity) => {
        this.aplicarIdentidadGoogle(identity);
        this.aplicarModoGoogle();

        this.cargando = false;
        this.cdr.markForCheck();
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

  contrasenasCoinciden(): boolean {
    const clave = this.form.get('clave')?.value;
    const confirmar = this.form.get('confirmar_clave')?.value;
    return clave === confirmar;
  }

  private cargarInstituciones(): void {
    this.cargandoInstituciones = true;

    this.institucionesService.obtenerTodas().subscribe({
      next: (instituciones) => {
        setTimeout(() => {
          this.instituciones = instituciones;
          this.cargandoInstituciones = false;
          this.cdr.markForCheck();
        }, 0);
      },
      error: () => {
        setTimeout(() => {
          this.cargandoInstituciones = false;
          this.error = 'No fue posible cargar las instituciones';
          this.cdr.markForCheck();
        }, 0);
      }
    });
  }

  irALogin(): void {
    this.switchToLogin.emit();
  }

  cancelar(): void {
    this.cancel.emit();
  }

  private mostrarMensaje(mensaje: string, tipo: 'success' | 'error' | 'info'): void {
    const panelClass =
      tipo === 'success' ? 'snackbar-success' :
      tipo === 'error' ? 'snackbar-error' :
      'snackbar-info';

    this.snackBar.open(mensaje, 'Cerrar', {
      duration: 4000,
      horizontalPosition: 'center',
      verticalPosition: 'top',
      panelClass
    });
  }

  private buildGooglePayload(): GoogleRegisterRequest | null {
    if (!this.googleIdToken) {
      return null;
    }

    const payload: GoogleRegisterRequest = {
      mode: 'register',
      id_token: this.googleIdToken,
      nombre: (this.form.value.nombre ?? '').trim(),
      apellido: (this.form.value.apellido ?? '').trim(),
      dni: (this.form.value.dni ?? '').trim(),
      fecha_nacimiento: (this.form.value.fecha_nacimiento ?? '').trim(),
      telefono: (this.form.value.telefono ?? '').trim(),
      id_institucion: Number(this.form.value.id_institucion ?? 0),
      id_carrera: Number(this.form.value.id_carrera ?? 0),
      anio_cursada: Number(this.form.value.anio_cursada ?? 0)
    };

    if (this.googleFotoPerfilUrl) {
      payload.foto_perfil_url = this.googleFotoPerfilUrl;
    }

    if (
      payload.nombre === '' ||
      payload.apellido === '' ||
      payload.dni === '' ||
      payload.fecha_nacimiento === '' ||
      payload.telefono === '' ||
      payload.id_institucion <= 0 ||
      payload.id_carrera <= 0 ||
      payload.anio_cursada <= 0
    ) {
      return null;
    }

    return payload;
  }

  get googleFlowActivo(): boolean {
    return this.googleIdToken !== null;
  }

  get googleCamposFaltantes(): string[] {
    return [
      { key: 'nombre', label: 'Nombre' },
      { key: 'apellido', label: 'Apellido' },
      { key: 'dni', label: 'DNI' },
      { key: 'fecha_nacimiento', label: 'Fecha de nacimiento' },
      { key: 'telefono', label: 'Telefono' },
      { key: 'id_institucion', label: 'Institucion' },
      { key: 'id_carrera', label: 'Carrera' },
      { key: 'anio_cursada', label: 'Año de cursada' }
    ].filter((field) => this.estaCampoFaltante(field.key)).map((field) => field.label);
  }

  get puedeFinalizarConGoogle(): boolean {
    return this.googleFlowActivo && this.googleCamposFaltantes.length === 0;
  }

  private finalizarRegistroConGoogle(): void {
    const payload = this.buildGooglePayload();
    if (!payload) {
      this.actualizarAyudaFlujoGoogle();
      this.error = null;
      this.cdr.markForCheck();
      return;
    }

    this.cargando = true;
    this.error = null;
    this.googleHelperMessage = null;

    this.authService.registerWithGoogleToken(payload).subscribe({
      next: (response) => {
        setTimeout(() => {
          this.cargando = false;
          this.error = null;
          this.mostrarMensaje(
            response.message || 'Registro con Google exitoso. Quedo pendiente de aprobacion.',
            'success'
          );
          this.cancel.emit();
          this.cdr.markForCheck();
        }, 0);
      },
      error: (err: HttpErrorResponse | Error) => {
        setTimeout(() => {
          this.cargando = false;
          this.error = (err as HttpErrorResponse)?.error?.message || err.message || 'Error al registrar con Google';
          this.cdr.markForCheck();
        }, 0);
      }
    });
  }

  private actualizarAyudaFlujoGoogle(): void {
    const faltantes = this.googleCamposFaltantes;
    if (faltantes.length === 0) {
      this.googleHelperMessage = 'Listo. Ya podes finalizar el registro con Google.';
      return;
    }

    this.googleHelperMessage = `Te faltan ${faltantes.length} dato(s): ${faltantes.join(', ')}.`;
  }

  private estaCampoFaltante(key: string): boolean {
    const value = this.form.get(key)?.value;

    if (key === 'id_institucion') {
      return Number(value ?? 0) <= 0;
    }

    if (key === 'id_carrera' || key === 'anio_cursada') {
      return Number(value ?? 0) <= 0;
    }

    return String(value ?? '').trim() === '';
  }

  private precargarDesdeGooglePendiente(): void {
    const identity = this.authService.consumePendingGoogleIdentity();
    if (!identity) {
      return;
    }

    this.aplicarIdentidadGoogle(identity);
    this.aplicarModoGoogle();
    this.googleHelperMessage = 'Completa los datos faltantes para terminar el registro con Google.';
    this.mostrarMensaje('Completá los datos faltantes para crear tu cuenta con Google.', 'info');
  }

  private aplicarIdentidadGoogle(identity: GoogleIdentity): void {
    this.googleIdToken = identity.idToken;
    this.googleFotoPerfilUrl = identity.fotoPerfilUrl ?? null;

    this.form.patchValue({
      nombre: identity.nombre || this.form.value.nombre,
      apellido: identity.apellido || this.form.value.apellido,
      email: identity.email || this.form.value.email
    });

    this.actualizarAyudaFlujoGoogle();
    this.cdr.markForCheck();
  }

  private aplicarModoGoogle(): void {
    this.form.get('clave')?.clearValidators();
    this.form.get('confirmar_clave')?.clearValidators();
    this.form.get('clave')?.setValue('');
    this.form.get('confirmar_clave')?.setValue('');
    this.form.get('clave')?.updateValueAndValidity();
    this.form.get('confirmar_clave')?.updateValueAndValidity();
  }

  private actualizarCarrerasDisponibles(idInstitucion: number): void {
    const institucion = this.instituciones.find((item) => item.id === idInstitucion) ?? null;
    this.carrerasDisponibles = institucion?.carreras ?? [];

    const idCarreraActual = Number(this.form.get('id_carrera')?.value ?? 0);
    const carreraSigueDisponible = this.carrerasDisponibles.some((carrera) => carrera.id === idCarreraActual);

    if (!carreraSigueDisponible) {
      this.form.get('id_carrera')?.setValue('');
    }

    this.cdr.markForCheck();
  }
}
