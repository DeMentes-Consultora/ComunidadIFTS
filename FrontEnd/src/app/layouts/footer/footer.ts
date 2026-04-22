import { Component, OnDestroy, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { MatDialog } from '@angular/material/dialog';
import { Subscription, interval } from 'rxjs';
import { SiteCarouselItem } from '../../shared/models/site-customization.model';
import { SiteCustomizationService } from '../../shared/services/site-customization.service';
import { Contacto } from '../../features/contacto/contacto';

@Component({
  selector: 'app-footer',
  imports: [CommonModule, RouterModule],
  templateUrl: './footer.html',
  styleUrl: './footer.css',
})
export class Footer implements OnInit, OnDestroy {
  private readonly siteCustomizationService = inject(SiteCustomizationService);
  private readonly dialog = inject(MatDialog);
  private shopTimerSub: Subscription | null = null;
  readonly maxShopSlidesVisible = 6;

  currentYear: number = 0;
  shopSlides: SiteCarouselItem[] = [];
  visibleShopSlides: SiteCarouselItem[] = [];
  showShopControls = false;
  currentShopSlideIndex = 0;
  footerBrandingText = 'Desarrollado por DeMentesConsultora';
  footerBrandingLogoUrl: string | null = null;
  footerBrandingLinkUrl: string | null = null;

  ngOnInit(): void {
    this.currentYear = new Date().getFullYear();
    this.siteCustomizationService.loadPublicConfig().subscribe({
      next: (config) => {
        this.shopSlides = (config.shop_carousel ?? []).filter((slide) => slide.habilitado === 1);
        this.footerBrandingText = (config.footer_branding?.developer_text ?? 'Desarrollado por DeMentesConsultora').trim() || 'Desarrollado por DeMentesConsultora';
        this.footerBrandingLogoUrl = config.footer_branding?.logo_url || null;
        this.footerBrandingLinkUrl = config.footer_branding?.link_url || null;
        this.currentShopSlideIndex = 0;
        this.refreshVisibleShopSlides();
        this.startShopAutoplay();
      },
      error: () => {
        this.shopSlides = [];
        this.visibleShopSlides = [];
        this.showShopControls = false;
        this.footerBrandingText = 'Desarrollado por DeMentesConsultora';
        this.footerBrandingLogoUrl = null;
        this.footerBrandingLinkUrl = null;
      },
    });
  }

  ngOnDestroy(): void {
    this.stopShopAutoplay();
  }

  goToShopSlide(index: number): void {
    if (!this.shopSlides.length) {
      return;
    }

    this.currentShopSlideIndex = (index + this.shopSlides.length) % this.shopSlides.length;
    this.refreshVisibleShopSlides();
  }

  prevShopSlide(): void {
    this.goToShopSlide(this.currentShopSlideIndex - 1);
  }

  nextShopSlide(): void {
    this.goToShopSlide(this.currentShopSlideIndex + 1);
  }

  openContacto(event: Event): void {
    if (typeof window === 'undefined' || window.matchMedia('(max-width: 768px)').matches) {
      return;
    }

    event.preventDefault();
    this.dialog.open(Contacto, {
      panelClass: 'contacto-dialog-panel',
      data: { modal: true },
      maxWidth: '860px',
      width: '92vw',
      autoFocus: false,
    });
  }

  private startShopAutoplay(): void {
    this.stopShopAutoplay();
    if (this.shopSlides.length <= this.maxShopSlidesVisible) {
      return;
    }

    this.shopTimerSub = interval(5000).subscribe(() => {
      this.nextShopSlide();
    });
  }

  private stopShopAutoplay(): void {
    if (!this.shopTimerSub) {
      return;
    }

    this.shopTimerSub.unsubscribe();
    this.shopTimerSub = null;
  }

  private refreshVisibleShopSlides(): void {
    this.showShopControls = this.shopSlides.length > this.maxShopSlidesVisible;

    if (!this.shopSlides.length) {
      this.visibleShopSlides = [];
      return;
    }

    const visibleCount = Math.min(this.maxShopSlidesVisible, this.shopSlides.length);
    const slides: SiteCarouselItem[] = [];

    for (let i = 0; i < visibleCount; i++) {
      const index = (this.currentShopSlideIndex + i) % this.shopSlides.length;
      const slide = this.shopSlides[index];
      if (slide) {
        slides.push(slide);
      }
    }

    this.visibleShopSlides = slides;
  }
}
