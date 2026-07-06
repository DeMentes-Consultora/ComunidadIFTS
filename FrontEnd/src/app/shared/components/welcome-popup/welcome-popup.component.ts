import { Component, Inject, OnInit, OnDestroy, NgZone } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatDialogRef, MAT_DIALOG_DATA, MatDialogModule } from '@angular/material/dialog';
import confetti from 'canvas-confetti';

@Component({
  selector: 'app-welcome-popup',
  standalone: true,
  imports: [CommonModule, MatDialogModule],
  templateUrl: './welcome-popup.component.html',
  styleUrl: './welcome-popup.component.css'
})
export class WelcomePopupComponent implements OnInit, OnDestroy {
  private timerId: any;
  progress = 0;
  private progressInterval: any;

  constructor(
    private dialogRef: MatDialogRef<WelcomePopupComponent>,
    private zone: NgZone
  ) {
    this.dialogRef.disableClose = false;
  }

  ngOnInit() {
    this.launchConfetti();

    setTimeout(() => {
      this.launchConfettiSoft();
    }, 5000);

    this.zone.runOutsideAngular(() => {
      const duration = 10000;
      const start = Date.now();

      this.progressInterval = setInterval(() => {
        const elapsed = Date.now() - start;
        this.progress = Math.min((elapsed / duration) * 100, 100);
      }, 50);
    });

    this.timerId = setTimeout(() => {
      this.close();
    }, 10000);
  }

  ngOnDestroy() {
    if (this.timerId) {
      clearTimeout(this.timerId);
    }
    if (this.progressInterval) {
      clearInterval(this.progressInterval);
    }
  }

  close() {
    this.dialogRef.close();
  }

  private launchConfetti() {
    const duration = 3000;
    const end = Date.now() + duration;
    const colors = ['#006633', '#e53935', '#1e88e5', '#fb8c00', '#43a047', '#8e24aa'];

    const frame = () => {
      confetti({
        particleCount: 3,
        angle: 60,
        spread: 65,
        origin: { x: 0, y: 0.65 },
        colors
      });
      confetti({
        particleCount: 3,
        angle: 120,
        spread: 65,
        origin: { x: 1, y: 0.65 },
        colors
      });

      if (Date.now() < end) {
        requestAnimationFrame(frame);
      }
    };

    frame();
  }

  private launchConfettiSoft() {
    confetti({
      particleCount: 80,
      spread: 100,
      origin: { y: 0.6 },
      colors: ['#006633', '#e53935', '#1e88e5', '#fb8c00']
    });
  }
}
