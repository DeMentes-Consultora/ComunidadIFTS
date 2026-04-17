import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormBuilder, Validators } from '@angular/forms';
import { MatCardModule } from '@angular/material/card';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatSnackBar, MatSnackBarModule } from '@angular/material/snack-bar';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { AuthService } from '../../../shared/services/auth.service';
import { BolsaTrabajoService } from '../../../shared/services/bolsa-trabajo.service';

@Component({
  selector: 'app-crear-oferta',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    MatCardModule,
    MatFormFieldModule,
    MatInputModule,
    MatButtonModule,
    MatIconModule,
    MatSnackBarModule,
    MatProgressSpinnerModule
  ],
  templateUrl: './crear-oferta.html',
  styleUrl: './crear-oferta.css'
})
export class CrearOferta implements OnInit {
  private fb          = inject(FormBuilder);
  private bolsaSvc    = inject(BolsaTrabajoService);
  private authSvc     = inject(AuthService);
  private snackBar    = inject(MatSnackBar);

  enviando = false;
  ofertaEnviada = false;

  form = this.fb.group({
    titulo: ['', [Validators.required, Validators.maxLength(512)]],
    texto:  ['', [Validators.required, Validators.maxLength(512)]]
  });

  usuario$ = this.authSvc.currentUser$;

  ngOnInit(): void {}

  enviar(): void {
    if (this.form.invalid || this.enviando) return;

    this.enviando = true;
    const { titulo, texto } = this.form.value;

    this.bolsaSvc.crearOferta({ tituloOferta: titulo!, textoOferta: texto! }).subscribe({
      next: (res) => {
        if (res.success) {
          this.ofertaEnviada = true;
          this.form.reset();
          this.snackBar.open('Oferta enviada. Recibirás un email cuando sea publicada.', 'Cerrar', {
            duration: 6000,
            horizontalPosition: 'center',
            verticalPosition: 'top',
            panelClass: 'snackbar-success'
          });
        } else {
          this.mostrarError(res.message || 'Error al enviar la oferta');
        }
        this.enviando = false;
      },
      error: (err) => {
        this.mostrarError(err?.error?.message || 'Error al enviar la oferta');
        this.enviando = false;
      }
    });
  }

  nuevaOferta(): void {
    this.ofertaEnviada = false;
    this.form.reset();
  }

  private mostrarError(msg: string): void {
    this.snackBar.open(msg, 'Cerrar', {
      duration: 5000,
      horizontalPosition: 'center',
      verticalPosition: 'top',
      panelClass: 'snackbar-error'
    });
  }
}
