import { Component, Input } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { MatCardModule } from '@angular/material/card';

@Component({
  selector: 'app-auth-shell',
  standalone: true,
  imports: [CommonModule, RouterModule, MatCardModule],
  templateUrl: './auth-shell.html',
  styleUrl: './auth-shell.css'
})
export class AuthShellComponent {
  @Input() title = '';
  @Input() subtitle = '';
  @Input() cardWidth = '520px';
}