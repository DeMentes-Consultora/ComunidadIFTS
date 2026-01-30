import { Component, Input, Output, EventEmitter, OnInit, ChangeDetectionStrategy, ChangeDetectorRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule, ReactiveFormsModule, FormBuilder, FormGroup, Validators } from '@angular/forms';
import { MatDialogModule, MatDialogRef } from '@angular/material/dialog';
import { MatButtonModule } from '@angular/material/button';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatChipsModule } from '@angular/material/chips';
import { MatCheckboxModule } from '@angular/material/checkbox';
import { MatIconModule } from '@angular/material/icon';
import * as L from 'leaflet';
import { InstitucionesService } from '../../services/instituciones.service';

@Component({
  selector: 'app-formulario-institucion',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    ReactiveFormsModule,
    MatDialogModule,
    MatButtonModule,
    MatFormFieldModule,
    MatInputModule,
    MatSelectModule,
    MatChipsModule,
    MatCheckboxModule,
    MatIconModule
  ],
  templateUrl: './formulario-institucion.html',
  styleUrls: ['./formulario-institucion.css'],
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class FormularioInstitucionComponent implements OnInit {
  @Input() coordenadas: L.LatLng | null = null;
  @Input() direccion: string = '';
  @Output() institucionGuardada = new EventEmitter<any>();

  formulario!: FormGroup;
  carrerasDisponibles: string[] = [];
  carrerasSeleccionadas: string[] = [];
  logoPreview: string | null = null;
  cargando = false;
  error: string | null = null;

  constructor(
    private fb: FormBuilder,
    private institucionesService: InstitucionesService,
    private cdr: ChangeDetectorRef,
    public dialogRef: MatDialogRef<FormularioInstitucionComponent>
  ) {
    this.inicializarFormulario();
  }

  ngOnInit(): void {
    this.cargarCarreras();
    if (this.coordenadas) {
      this.formulario.patchValue({
        latitud_ifts: this.coordenadas.lat,
        longitud_ifts: this.coordenadas.lng
      });
    }
    if (this.direccion) {
      this.formulario.patchValue({
        direccion_ifts: this.direccion
      });
    }
  }

  private inicializarFormulario(): void {
    this.formulario = this.fb.group({
      nombre_ifts: ['', [Validators.required, Validators.minLength(3)]],
      direccion_ifts: ['', [Validators.minLength(5)]],
      telefono_ifts: ['', [Validators.required]],
      email_ifts: ['', [Validators.required, Validators.email]],
      sitio_web_ifts: ['', [Validators.required]],
      observaciones_ifts: ['', [Validators.maxLength(255)]],
      latitud_ifts: [{ value: '', disabled: true }],
      longitud_ifts: [{ value: '', disabled: true }],
      logo_ifts: ['']
    });
  }

  private cargarCarreras(): void {
    this.institucionesService.obtenerCarreras().subscribe({
      next: (carreras) => {
        this.carrerasDisponibles = carreras;
        this.cdr.markForCheck();
      },
      error: (err) => {
        console.error('Error cargando carreras:', err);
      }
    });
  }

  onLogoSeleccionado(event: any): void {
    const archivo = event.target.files[0];
    if (archivo) {
      const reader = new FileReader();
      reader.onload = (e: any) => {
        this.logoPreview = e.target.result;
        this.formulario.patchValue({
          logo_ifts: e.target.result
        });
        this.cdr.markForCheck();
      };
      reader.readAsDataURL(archivo);
    }
  }

  agregarCarrera(carrera: string): void {
    if (carrera && !this.carrerasSeleccionadas.includes(carrera)) {
      this.carrerasSeleccionadas.push(carrera);
      this.cdr.markForCheck();
    }
  }

  eliminarCarrera(carrera: string): void {
    this.carrerasSeleccionadas = this.carrerasSeleccionadas.filter(c => c !== carrera);
    this.cdr.markForCheck();
  }

  toggleCarrera(carrera: string): void {
    const index = this.carrerasSeleccionadas.indexOf(carrera);
    if (index > -1) {
      this.carrerasSeleccionadas.splice(index, 1);
    } else {
      this.carrerasSeleccionadas.push(carrera);
    }
    this.cdr.markForCheck();
  }

  onSubmit(): void {
    // Normalizar sitio web: agregar https:// si no tiene protocolo
    const sitioWeb = this.formulario.get('sitio_web_ifts')?.value;
    if (sitioWeb && !sitioWeb.startsWith('http://') && !sitioWeb.startsWith('https://')) {
      this.formulario.patchValue({
        sitio_web_ifts: 'https://' + sitioWeb
      });
    }

    // Logging para debug
    console.log('Formulario válido:', this.formulario.valid);
    console.log('Errores del formulario:', this.formulario.errors);
    Object.keys(this.formulario.controls).forEach(key => {
      const control = this.formulario.get(key);
      if (control?.invalid) {
        console.log(`Campo "${key}" inválido:`, control.errors);
      }
    });

    if (this.formulario.invalid || !this.coordenadas) {
      this.error = 'Por favor completa todos los campos requeridos';
      this.cdr.markForCheck();
      return;
    }

    if (this.carrerasSeleccionadas.length === 0) {
      this.error = 'Selecciona al menos una carrera';
      this.cdr.markForCheck();
      return;
    }

    this.cargando = true;
    this.error = null;

    const datosFormulario = this.formulario.getRawValue();
    const datos = {
      ...datosFormulario,
      // Campos con y sin sufijo para compatibilidad
      nombre: datosFormulario.nombre_ifts,
      direccion: datosFormulario.direccion_ifts,
      telefono: datosFormulario.telefono_ifts,
      email: datosFormulario.email_ifts,
      sitio_web: datosFormulario.sitio_web_ifts,
      observaciones: datosFormulario.observaciones_ifts,
      logo: datosFormulario.logo_ifts,
      latitud: this.coordenadas.lat,
      longitud: this.coordenadas.lng,
      latitud_ifts: this.coordenadas.lat,
      longitud_ifts: this.coordenadas.lng,
      carreras: this.carrerasSeleccionadas
    };

    console.log('Datos a enviar:', datos);

    this.institucionesService.guardarInstitucion(datos).subscribe({
      next: (respuesta) => {
        console.log('Respuesta del backend:', respuesta);
        this.cargando = false;
        this.institucionGuardada.emit(respuesta);
        this.dialogRef.close(respuesta);
      },
      error: (err) => {
        this.cargando = false;
        this.error = err.error?.message || 'Error al guardar la institución';
        console.error('Error guardando institución:', err);
        this.cdr.markForCheck();
      }
    });
  }

  cancelar(): void {
    this.dialogRef.close();
  }
}
