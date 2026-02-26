import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject, Observable, throwError } from 'rxjs';
import { catchError, map, tap } from 'rxjs/operators';
import { environment } from '../../../environments/environment';
import { AuthResponse, AuthUser, BasicAuthResponse, LoginRequest, RegisterRequest, RegisterResponse } from '../models/auth.model';

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private apiLoginUrl = `${environment.apiUrl}/login.php`;
  private apiRegisterUrl = `${environment.apiUrl}/register.php`;
  private apiLogoutUrl = `${environment.apiUrl}/logout.php`;
  private storageKey = 'comunidadifts.auth.user';
  private currentUserSubject = new BehaviorSubject<AuthUser | null>(this.getStoredUser());

  currentUser$ = this.currentUserSubject.asObservable();

  constructor(private http: HttpClient) {}

  login(credentials: LoginRequest): Observable<AuthUser> {
    return this.http.post<AuthResponse>(this.apiLoginUrl, credentials, { withCredentials: true })
      .pipe(
        map(response => {
          if (!response.success || !response.data) {
            throw new Error(response.message || 'No fue posible iniciar sesión');
          }
          return response.data;
        }),
        tap(user => {
          localStorage.setItem(this.storageKey, JSON.stringify(user));
          this.currentUserSubject.next(user);
        }),
        catchError(err => {
          return throwError(() => err);
        })
      );
  }

  register(payload: RegisterRequest): Observable<RegisterResponse> {
    return this.http.post<RegisterResponse>(this.apiRegisterUrl, payload, { withCredentials: true })
      .pipe(
        map(response => {
          if (!response.success) {
            throw new Error(response.message || 'No fue posible registrarse');
          }
          return response;
        }),
        tap(response => {
          if (response.data && !response.pendiente_aprobacion) {
            localStorage.setItem(this.storageKey, JSON.stringify(response.data));
            this.currentUserSubject.next(response.data);
          }
        }),
        catchError(err => {
          const message = err?.error?.message || err?.message || 'Error de registro';
          return throwError(() => new Error(message));
        })
      );
  }

  logout(): Observable<boolean> {
    return this.http.post<BasicAuthResponse>(this.apiLogoutUrl, {}, { withCredentials: true })
      .pipe(
        map(response => !!response.success),
        tap(() => this.clearLocalSession()),
        catchError(() => {
          this.clearLocalSession();
          return throwError(() => new Error('No fue posible cerrar sesión en el servidor'));
        })
      );
  }

  getCurrentUser(): AuthUser | null {
    return this.currentUserSubject.value;
  }

  isAuthenticated(): boolean {
    return this.getCurrentUser() !== null;
  }

  private getStoredUser(): AuthUser | null {
    const raw = localStorage.getItem(this.storageKey);
    if (!raw) {
      return null;
    }

    try {
      return JSON.parse(raw) as AuthUser;
    } catch {
      localStorage.removeItem(this.storageKey);
      return null;
    }
  }

  private clearLocalSession(): void {
    localStorage.removeItem(this.storageKey);
    this.currentUserSubject.next(null);
  }
}
