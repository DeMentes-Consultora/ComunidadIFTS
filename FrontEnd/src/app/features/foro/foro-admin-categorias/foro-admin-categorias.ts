import { ChangeDetectionStrategy, Component, inject, OnInit, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule, ReactiveFormsModule, FormBuilder, FormGroup, Validators } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { MatSnackBar, MatSnackBarModule } from '@angular/material/snack-bar';
import { MatListModule } from '@angular/material/list';
import { MatDividerModule } from '@angular/material/divider';
import { ForoCategoria } from '../../../shared/models/foro.model';
import { ForoService } from '../../../shared/services/foro.service';

@Component({
  selector: 'app-foro-admin-categorias',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    ReactiveFormsModule,
    MatCardModule,
    MatButtonModule,
    MatIconModule,
    MatFormFieldModule,
    MatInputModule,
    MatSnackBarModule,
    MatListModule,
    MatDividerModule
  ],
  templateUrl: './foro-admin-categorias.html',
  styleUrl: './foro-admin-categorias.css',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class ForoAdminCategoriasComponent implements OnInit {
  private readonly foroService = inject(ForoService);
  private readonly fb = inject(FormBuilder);
  private readonly snackBar = inject(MatSnackBar);

  readonly categorias = signal<ForoCategoria[]>([]);
  readonly cargando = signal(false);
  readonly mostrandoFormulario = signal(false);
  readonly editandoId = signal<number | null>(null);

  readonly form: FormGroup = this.fb.group({
    nombre: ['', [Validators.required, Validators.maxLength(150)]],
    descripcion: ['', [Validators.maxLength(500)]],
    icono: ['', [Validators.maxLength(50)]],
    color: ['#3f51b5'],
    orden: [0, [Validators.min(0)]]
  });

  ngOnInit(): void {
    this.cargarCategorias();
  }

  cargarCategorias(): void {
    this.cargando.set(true);
    this.foroService.getCategorias(true).subscribe({
      next: (cats) => {
        this.categorias.set(cats);
        this.cargando.set(false);
      },
      error: () => {
        this.cargando.set(false);
        this.snackBar.open('Error al cargar categorías', 'Cerrar', { duration: 3000 });
      }
    });
  }

  abrirFormulario(): void {
    this.mostrandoFormulario.set(true);
    this.editandoId.set(null);
    this.form.reset({ nombre: '', descripcion: '', icono: '', color: '#3f51b5', orden: 0 });
  }

  editarCategoria(cat: ForoCategoria): void {
    this.mostrandoFormulario.set(true);
    this.editandoId.set(cat.id_categoria);
    this.form.patchValue({
      nombre: cat.nombre,
      descripcion: cat.descripcion ?? '',
      icono: cat.icono ?? '',
      color: cat.color,
      orden: cat.orden
    });
  }

  cancelarFormulario(): void {
    this.mostrandoFormulario.set(false);
    this.editandoId.set(null);
    this.form.reset();
  }

  guardar(): void {
    if (this.form.invalid) return;

    const datos = this.form.value;
    const editId = this.editandoId();

    if (editId) {
      this.foroService.actualizarCategoria({ id_categoria: editId, ...datos }).subscribe({
        next: () => {
          this.snackBar.open('Categoría actualizada', 'Cerrar', { duration: 3000 });
          this.cancelarFormulario();
          this.cargarCategorias();
        },
        error: (err) => {
          this.snackBar.open(err.error?.message || 'Error al actualizar', 'Cerrar', { duration: 3000 });
        }
      });
    } else {
      this.foroService.crearCategoria(datos).subscribe({
        next: () => {
          this.snackBar.open('Categoría creada', 'Cerrar', { duration: 3000 });
          this.cancelarFormulario();
          this.cargarCategorias();
        },
        error: (err) => {
          this.snackBar.open(err.error?.message || 'Error al crear', 'Cerrar', { duration: 3000 });
        }
      });
    }
  }

  eliminarCategoria(cat: ForoCategoria): void {
    if (!confirm(`¿Eliminar la categoría "${cat.nombre}"?`)) return;

    this.foroService.eliminarCategoria(cat.id_categoria).subscribe({
      next: () => {
        this.snackBar.open('Categoría eliminada', 'Cerrar', { duration: 3000 });
        this.cargarCategorias();
      },
      error: (err) => {
        this.snackBar.open(err.error?.message || 'Error al eliminar', 'Cerrar', { duration: 3000 });
      }
    });
  }
}
