import { Component, EventEmitter, OnInit, Output, ChangeDetectorRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { MatCardModule } from '@angular/material/card';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { MatSelectModule } from '@angular/material/select';
import { MatIconModule } from '@angular/material/icon';
import { AuthService } from '../../services/auth.service';
import { AuthUser } from '../../models/auth.model';
import { Institucion } from '../../models/institucion.model';
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
    MatIconModule
  ],
  templateUrl: './formulario-registro.html',
  styleUrl: './formulario-registro.css'
})
export class FormularioRegistroComponent implements OnInit {
  @Output() registerSuccess = new EventEmitter<AuthUser>();
  @Output() switchToLogin = new EventEmitter<void>();
  @Output() cancel = new EventEmitter<void>();

  instituciones: Institucion[] = [];
  cargando = false;
  cargandoInstituciones = false;
  error: string | null = null;
  ocultarClave = true;
  ocultarConfirmarClave = true;

  form;

  constructor(
    private fb: FormBuilder,
    private authService: AuthService,
    private institucionesService: InstitucionesService,
    private cdr: ChangeDetectorRef
  ) {
    this.form = this.fb.group({
      nombre: ['', [Validators.required, Validators.minLength(2)]],
      apellido: ['', [Validators.required, Validators.minLength(2)]],
      dni: ['', [Validators.required, Validators.pattern(/^\d{7,9}$/)]],
      fecha_nacimiento: ['', [Validators.required]],
      telefono: ['', [Validators.required, Validators.minLength(8)]],
      id_institucion: ['', [Validators.required]],
      email: ['', [Validators.required, Validators.email]],
      clave: ['', [Validators.required, Validators.minLength(6)]],
      confirmar_clave: ['', [Validators.required]]
    });
  }

  ngOnInit(): void {
    this.cargarInstituciones();
  }

  onSubmit(): void {
    if (this.form.invalid || this.cargando) {
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
            // Mostrar mensaje de pendiente
            this.error = null;
            alert('✅ ' + (response.message || 'Registro exitoso. Tu solicitud está pendiente de aprobación por el administrador. Recibirás un email cuando sea aprobada.'));
            this.cancel.emit(); // Cerrar modal
          } else {
            // Usuario aprobado directamente (flujo antiguo por si acaso)
            this.registerSuccess.emit(response.data || response);
          }
        }, 0);
      },
      error: (err: Error) => {
        setTimeout(() => {
          this.cargando = false;
          this.error = err.message;
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
}
