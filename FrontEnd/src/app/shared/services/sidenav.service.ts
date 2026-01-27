import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class SidenavService {
  private sidenavOpenSubject = new BehaviorSubject<boolean>(false);
  public sidenavOpen$: Observable<boolean> = this.sidenavOpenSubject.asObservable();

  toggle() {
    this.sidenavOpenSubject.next(!this.sidenavOpenSubject.value);
  }

  open() {
    this.sidenavOpenSubject.next(true);
  }

  close() {
    this.sidenavOpenSubject.next(false);
  }
}
