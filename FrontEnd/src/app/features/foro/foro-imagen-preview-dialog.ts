import { CommonModule } from '@angular/common';
import { Component, Inject } from '@angular/core';
import { MAT_DIALOG_DATA, MatDialogModule, MatDialogRef } from '@angular/material/dialog';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';

export interface ForoImagenPreviewData {
  url: string;
  title: string;
}

@Component({
  selector: 'app-foro-imagen-preview-dialog',
  standalone: true,
  imports: [CommonModule, MatDialogModule, MatButtonModule, MatIconModule],
  template: `
    <div class="preview-shell">
      <div class="preview-header">
        <strong>{{ data.title }}</strong>
        <button mat-icon-button type="button" (click)="close()" aria-label="Cerrar vista previa">
          <mat-icon>close</mat-icon>
        </button>
      </div>

      <div class="preview-body">
        <img [src]="data.url" [alt]="data.title" [title]="data.title">
      </div>
    </div>
  `,
  styles: [`
    :host {
      display: block;
      max-width: 95vw;
    }

    .preview-shell {
      background: #111827;
      color: #fff;
      border-radius: 16px;
      overflow: hidden;
    }

    .preview-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
      padding: 12px 16px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.12);
    }

    .preview-header strong {
      font-size: 14px;
      font-weight: 600;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .preview-body {
      padding: 16px;
      display: flex;
      justify-content: center;
      align-items: center;
      background: #0f172a;
      max-height: 85vh;
    }

    .preview-body img {
      max-width: 100%;
      max-height: 80vh;
      object-fit: contain;
      border-radius: 12px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.35);
    }
  `]
})
export class ForoImagenPreviewDialogComponent {
  constructor(
    private readonly dialogRef: MatDialogRef<ForoImagenPreviewDialogComponent>,
    @Inject(MAT_DIALOG_DATA) public readonly data: ForoImagenPreviewData,
  ) {}

  close(): void {
    this.dialogRef.close();
  }
}