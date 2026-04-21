import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { SiteCarouselItem } from '../../shared/models/site-customization.model';
import { SiteCustomizationService } from '../../shared/services/site-customization.service';

@Component({
  selector: 'app-tienda',
  standalone: true,
  imports: [CommonModule, RouterModule],
  template: `
    <main class="tienda-page">
      <header class="tienda-header">
        <p class="eyebrow">Comunidad IFTS</p>
        <h1>Tienda</h1>
        <p>Explora productos y recursos cargados desde el dashboard.</p>
      </header>

      @if (cargando) {
        <section class="empty-state">
          <p>Cargando tienda...</p>
        </section>
      } @else if (!productos.length) {
        <section class="empty-state">
          <h2>Aun no hay productos publicados</h2>
          <p>Cuando agregues imagenes desde el dashboard, apareceran aqui en formato de cards.</p>
        </section>
      } @else {
        <section class="productos-grid">
          @for (producto of productos; track producto.id_carrousel) {
            <article class="producto-card">
              <div class="producto-image-wrap">
                @if (producto.foto_perfil_url) {
                  <img [src]="producto.foto_perfil_url" [alt]="producto.titulo || 'Producto tienda'" title="Producto tienda" loading="lazy" />
                } @else {
                  <div class="producto-empty">Sin imagen</div>
                }
              </div>

              <div class="producto-content">
                <h2>{{ producto.titulo || 'Producto sin titulo' }}</h2>
                <p>{{ producto.descripcion || 'Descripcion pendiente.' }}</p>

                @if (producto.enlace) {
                  <a class="producto-link" [href]="producto.enlace" target="_blank" rel="noopener">Ver mas</a>
                }
              </div>
            </article>
          }
        </section>
      }
    </main>
  `,
  styles: [`
    .tienda-page {
      min-height: calc(100vh - 64px);
      padding: 96px 24px 32px;
      background:
        radial-gradient(circle at 80% 10%, rgba(255, 215, 0, 0.15), transparent 28%),
        radial-gradient(circle at 10% 20%, rgba(34, 139, 34, 0.16), transparent 22%),
        linear-gradient(180deg, #f8fbf8 0%, #eef5ee 100%);
    }

    .tienda-header {
      max-width: 920px;
      margin: 0 auto 28px;
      text-align: center;
    }

    .tienda-header .eyebrow {
      margin: 0;
      text-transform: uppercase;
      letter-spacing: 0.15em;
      font-size: 0.74rem;
      color: #52735a;
    }

    .tienda-header h1 {
      margin: 8px 0 10px;
      font-size: clamp(2rem, 3.4vw, 3rem);
      color: #173827;
    }

    .tienda-header p {
      margin: 0;
      color: #4f6556;
    }

    .productos-grid {
      max-width: 1180px;
      margin: 0 auto;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 18px;
    }

    .producto-card {
      background: #fff;
      border-radius: 18px;
      overflow: hidden;
      border: 1px solid #d7e7d9;
      box-shadow: 0 10px 22px rgba(24, 56, 37, 0.1);
      display: flex;
      flex-direction: column;
    }

    .producto-image-wrap {
      height: 180px;
      background: #edf3ee;
    }

    .producto-image-wrap img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .producto-empty {
      width: 100%;
      height: 100%;
      display: grid;
      place-items: center;
      color: #6c8070;
    }

    .producto-content {
      padding: 14px;
      display: grid;
      gap: 10px;
    }

    .producto-content h2 {
      margin: 0;
      color: #1a3a29;
      font-size: 1.06rem;
    }

    .producto-content p {
      margin: 0;
      color: #546a5a;
      font-size: 0.95rem;
      line-height: 1.4;
    }

    .producto-link {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 8px 12px;
      border-radius: 999px;
      background: #173827;
      color: #fff;
      text-decoration: none;
      width: fit-content;
    }

    .empty-state {
      max-width: 760px;
      margin: 20px auto;
      background: #fff;
      border: 1px dashed #b9cfbf;
      border-radius: 16px;
      padding: 28px;
      text-align: center;
      color: #4e6454;
    }

    @media (max-width: 768px) {
      .tienda-page {
        padding: 88px 14px 22px;
      }
    }
  `],
})
export class Tienda implements OnInit {
  private readonly siteCustomizationService = inject(SiteCustomizationService);

  productos: SiteCarouselItem[] = [];
  cargando = true;

  ngOnInit(): void {
    this.siteCustomizationService.loadPublicConfig(true).subscribe({
      next: (config) => {
        this.productos = (config.shop_gallery ?? []).filter((item) => item.habilitado === 1);
        this.cargando = false;
      },
      error: () => {
        this.productos = [];
        this.cargando = false;
      },
    });
  }
}
