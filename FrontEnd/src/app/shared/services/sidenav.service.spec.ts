import { SidenavService } from './sidenav.service';

describe('SidenavService', () => {
  it('should toggle the sidenav state', () => {
    const service = new SidenavService();
    const states: boolean[] = [];
    const subscription = service.sidenavOpen$.subscribe((isOpen) => {
      states.push(isOpen);
    });

    service.toggle();
    service.toggle();

    expect(states).toEqual([false, true, false]);
    subscription.unsubscribe();
  });
});