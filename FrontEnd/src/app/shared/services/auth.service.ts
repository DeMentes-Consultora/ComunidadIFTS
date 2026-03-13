import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Auth } from '@angular/fire/auth';
import { GoogleAuthProvider, signInWithPopup, signOut } from 'firebase/auth';
import { BehaviorSubject, Observable, from, throwError } from 'rxjs';
import { catchError, map, tap } from 'rxjs/operators';
import { environment } from '../../../environments/environment';
import {
  AuthResponse,
  AuthUser,
  BasicAuthResponse,
  GoogleIdentity,
  GoogleLoginRequest,
  GoogleRegisterRequest,
  LoginRequest,
  RegisterRequest,
  RegisterResponse
} from '../models/auth.model';

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private apiLoginUrl = `${environment.apiUrl}/login.php`;
  private apiRegisterUrl = `${environment.apiUrl}/register.php`;
  private apiLogoutUrl = `${environment.apiUrl}/logout.php`;
  private apiGoogleAuthUrl = `${environment.apiUrl}/google-auth.php`;
  private apiActualizarFotoPerfilUrl = `${environment.apiUrl}/actualizar-foto-perfil.php`;
  private storageKey = 'comunidadifts.auth.user';
  private currentUserSubject = new BehaviorSubject<AuthUser | null>(this.getStoredUser());
  private pendingGoogleIdentity: GoogleIdentity | null = null;

  currentUser$ = this.currentUserSubject.asObservable();

  constructor(
    private http: HttpClient,
    private firebaseAuth: Auth
  ) {}

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

  getGoogleIdentity(): Observable<GoogleIdentity> {
    const provider = new GoogleAuthProvider();
    provider.setCustomParameters({ prompt: 'select_account' });

    return from(signInWithPopup(this.firebaseAuth, provider)).pipe(
      map((result) => {
        const credential = GoogleAuthProvider.credentialFromResult(result);
        const googleIdToken = credential?.idToken ?? '';

        if (!googleIdToken) {
          throw new Error('Google no devolvio un token valido. Intenta nuevamente.');
        }

        const displayName = result.user.displayName ?? '';
        const [nombre = '', ...restoApellido] = displayName.trim().split(' ');

        void signOut(this.firebaseAuth);

        return {
          idToken: googleIdToken,
          email: result.user.email ?? '',
          nombre,
          apellido: restoApellido.join(' ').trim(),
          fotoPerfilUrl: result.user.photoURL ?? undefined
        } as GoogleIdentity;
      }),
      catchError((err: unknown) => {
        const firebaseErr = err as { code?: string };
        const code = firebaseErr?.code ?? '';

        if (code === 'auth/popup-closed-by-user') {
          return throwError(() => new Error('Cancelaste el inicio con Google'));
        }

        if (code === 'auth/popup-blocked') {
          return throwError(() => new Error('Tu navegador bloqueó la ventana emergente de Google'));
        }

        return throwError(() => new Error('No fue posible autenticar con Google'));
      })
    );
  }

  loginWithGoogleToken(idToken: string): Observable<AuthUser> {
    const payload: GoogleLoginRequest = {
      mode: 'login',
      id_token: idToken
    };

    return this.http.post(this.apiGoogleAuthUrl, payload, { withCredentials: true, responseType: 'text' }).pipe(
      map((rawResponse) => {
        const response = this.parseJsonResponse<AuthResponse>(rawResponse);
        if (!response.success || !response.data) {
          throw new Error(response.message || 'No fue posible iniciar sesión con Google');
        }
        return response.data;
      }),
      tap((user) => {
        localStorage.setItem(this.storageKey, JSON.stringify(user));
        this.currentUserSubject.next(user);
      }),
      catchError((err) => {
        const message = this.extractBackendErrorMessage(err, 'Error de autenticación con Google');
        return throwError(() => new Error(message));
      })
    );
  }

  registerWithGoogleToken(payload: GoogleRegisterRequest): Observable<RegisterResponse> {
    return this.http.post(this.apiGoogleAuthUrl, payload, { withCredentials: true, responseType: 'text' }).pipe(
      map((rawResponse) => {
        const response = this.parseJsonResponse<RegisterResponse>(rawResponse);
        if (!response.success) {
          throw new Error(response.message || 'No fue posible registrarse con Google');
        }
        return response;
      }),
      catchError((err) => {
        const message = this.extractBackendErrorMessage(err, 'Error de registro con Google');
        return throwError(() => new Error(message));
      })
    );
  }

  setPendingGoogleIdentity(identity: GoogleIdentity): void {
    this.pendingGoogleIdentity = identity;
  }

  consumePendingGoogleIdentity(): GoogleIdentity | null {
    const identity = this.pendingGoogleIdentity;
    this.pendingGoogleIdentity = null;
    return identity;
  }

  actualizarFotoPerfil(file: File): Observable<AuthUser> {
    const formData = new FormData();
    formData.append('foto_perfil', file);

    return this.http.post<AuthResponse>(this.apiActualizarFotoPerfilUrl, formData, { withCredentials: true }).pipe(
      map((response) => {
        if (!response.success || !response.data) {
          throw new Error(response.message || 'No fue posible actualizar la foto de perfil');
        }
        return response.data;
      }),
      tap((user) => {
        localStorage.setItem(this.storageKey, JSON.stringify(user));
        this.currentUserSubject.next(user);
      }),
      catchError((err) => {
        const message = err?.error?.message || err?.message || 'Error al actualizar foto de perfil';
        return throwError(() => new Error(message));
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

  private parseJsonResponse<T>(rawResponse: unknown): T {
    if (typeof rawResponse !== 'string') {
      return rawResponse as T;
    }

    const text = rawResponse.replace(/^\uFEFF/, '').trim();
    if (text === '') {
      throw new Error('El servidor devolvió una respuesta vacía');
    }

    const parsedDirect = this.tryParseJson<T>(text);
    if (parsedDirect !== null) {
      return parsedDirect;
    }

    // Fallback para respuestas con warnings/notices antes o después del JSON.
    const start = text.indexOf('{');
    const end = text.lastIndexOf('}');
    if (start !== -1 && end > start) {
      const candidate = text.slice(start, end + 1);
      const parsedCandidate = this.tryParseJson<T>(candidate);
      if (parsedCandidate !== null) {
        return parsedCandidate;
      }
    }

    throw new Error('El servidor devolvió una respuesta inválida');
  }

  private extractBackendErrorMessage(err: any, fallback: string): string {
    const rawError = err?.error;

    if (typeof rawError === 'string') {
      const text = rawError.replace(/^\uFEFF/, '').trim();
      if (text !== '') {
        const parsed = this.tryParseJson<{ message?: string }>(text);
        if (parsed?.message) {
          return parsed.message;
        }

        const start = text.indexOf('{');
        const end = text.lastIndexOf('}');
        if (start !== -1 && end > start) {
          const parsedCandidate = this.tryParseJson<{ message?: string }>(text.slice(start, end + 1));
          if (parsedCandidate?.message) {
            return parsedCandidate.message;
          }
        }

        return text;
      }
    }

    return err?.error?.message || err?.message || fallback;
  }

  private tryParseJson<T>(text: string): T | null {
    try {
      return JSON.parse(text) as T;
    } catch {
      return null;
    }
  }
}
