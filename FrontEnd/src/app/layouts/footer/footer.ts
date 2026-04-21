import { Component, OnDestroy, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { Subscription, interval } from 'rxjs';
import { SiteCarouselItem } from '../../shared/models/site-customization.model';
import { SiteCustomizationService } from '../../shared/services/site-customization.service';

@Component({
  selector: 'app-footer',
  imports: [CommonModule, RouterModule],
  templateUrl: './footer.html',
  styleUrl: './footer.css',
})
export class Footer implements OnInit, OnDestroy {
  private readonly siteCustomizationService = inject(SiteCustomizationService);
  private shopTimerSub: Subscription | null = null;

  currentYear: number = 0;
  shopSlides: SiteCarouselItem[] = [];
  currentShopSlideIndex = 0;

  ngOnInit(): void {
    this.currentYear = new Date().getFullYear();
    this.siteCustomizationService.loadPublicConfig().subscribe({
      next: (config) => {
        this.shopSlides = (config.shop_carousel ?? []).filter((slide) => slide.habilitado === 1);
        this.currentShopSlideIndex = 0;
        this.startShopAutoplay();
      },
      error: () => {
        this.shopSlides = [];
      },
    });
  }

  ngOnDestroy(): void {
    this.stopShopAutoplay();
  }

  openContactModal(): void {
    // TODO: Implementar modal de contacto
    console.log('Abrir modal de contacto');
  }

  get currentShopSlide(): SiteCarouselItem | null {
    if (!this.shopSlides.length) {
      return null;
    }

    return this.shopSlides[this.currentShopSlideIndex] ?? null;
  }

  goToShopSlide(index: number): void {
    if (!this.shopSlides.length) {
      return;
    }

    this.currentShopSlideIndex = (index + this.shopSlides.length) % this.shopSlides.length;
  }

  prevShopSlide(): void {
    this.goToShopSlide(this.currentShopSlideIndex - 1);
  }

  nextShopSlide(): void {
    this.goToShopSlide(this.currentShopSlideIndex + 1);
  }

  private startShopAutoplay(): void {
    this.stopShopAutoplay();
    if (this.shopSlides.length <= 1) {
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
}
