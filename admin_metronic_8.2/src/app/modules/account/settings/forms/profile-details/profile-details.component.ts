import { ChangeDetectorRef, Component, OnDestroy, OnInit } from '@angular/core';
import { BehaviorSubject, Subscription } from 'rxjs';
import { AuthService } from 'src/app/modules/auth';
import { StateService } from 'src/app/pages/state.service';
import { ToastrService } from 'ngx-toastr';

@Component({
  selector: 'app-profile-details',
  templateUrl: './profile-details.component.html',
})
export class ProfileDetailsComponent implements OnInit, OnDestroy {
  isLoading$: BehaviorSubject<boolean> = new BehaviorSubject<boolean>(false);
  isLoading: boolean;
  private unsubscribe: Subscription[] = [];

  userId: number;
  firstname: string = '';
  lastname: string = '';
  username: string = '';
  email: string = '';
  phone: string = '';
  roleName: string = '';
  avatarUrl: string = './assets/media/avatars/blank.png';

  constructor(
    private cdr: ChangeDetectorRef,
    private auth: AuthService,
    private stateService: StateService,
    private toastr: ToastrService
  ) {
    const loadingSubscr = this.isLoading$
      .asObservable()
      .subscribe((res) => (this.isLoading = res));
    this.unsubscribe.push(loadingSubscr);
  }

  ngOnInit(): void {
    const user = this.auth.currentUserValue;
    if (user) {
      this.userId = user.id;
      this.firstname = user.firstname || '';
      this.lastname = user.lastname || '';
      this.username = user.username || '';
      this.email = user.email || '';
      this.phone = user.phone || '';
      this.roleName = user.role || '';
      this.avatarUrl = user.pic || './assets/media/avatars/blank.png';
    }
  }

  saveSettings() {
    this.isLoading$.next(true);

    setTimeout(() => {
      const systemUsers = this.stateService.systemUsers$.value;
      const foundUser = systemUsers.find(u => u.id === this.userId);
      if (foundUser) {
        foundUser.name = `${this.firstname} ${this.lastname}`.trim();
        foundUser.firstname = this.firstname;
        foundUser.lastname = this.lastname;
        foundUser.username = this.username;
        foundUser.email = this.email;
        foundUser.phone = this.phone;
        this.stateService.saveSystemUser(foundUser);

        // Update current auth session
        const updatedUser = { ...this.auth.currentUserValue };
        updatedUser.fullname = foundUser.name;
        updatedUser.firstname = this.firstname;
        updatedUser.lastname = this.lastname;
        updatedUser.username = this.username;
        updatedUser.email = this.email;
        updatedUser.phone = this.phone;
        this.auth.currentUserValue = updatedUser as any;
      }

      this.isLoading$.next(false);
      this.toastr.success('Detalles del perfil actualizados correctamente.', 'Éxito');
      this.cdr.detectChanges();
    }, 1000);
  }

  ngOnDestroy() {
    this.unsubscribe.forEach((sb) => sb.unsubscribe());
  }
}
