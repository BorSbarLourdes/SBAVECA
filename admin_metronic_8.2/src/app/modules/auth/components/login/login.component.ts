import { Component, OnInit, OnDestroy, ChangeDetectorRef } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Subscription, Observable } from 'rxjs';
import { first } from 'rxjs/operators';
import { UserModel } from '../../models/user.model';
import { AuthService } from '../../services/auth.service';
import { ActivatedRoute, Router } from '@angular/router';
import { environment } from '../../../../../environments/environment';

declare var google: any;

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.scss'],
})
export class LoginComponent implements OnInit, OnDestroy {
  // KeenThemes mock, change it to:
  defaultAuth: any = {
    username: '',
    password: '',
    rememberMe: false
  };
  loginForm: FormGroup;
  hasError: boolean;
  returnUrl: string;
  isLoading$: Observable<boolean>;

  // private fields
  private unsubscribe: Subscription[] = []; // Read more: => https://brianflove.com/2016/12/11/anguar-2-unsubscribe-observables/

  constructor(
    private fb: FormBuilder,
    private authService: AuthService,
    private route: ActivatedRoute,
    private router: Router
  ) {
    this.isLoading$ = this.authService.isLoading$;
    // redirect to home if already logged in
    if (this.authService.currentUserValue) {
      this.router.navigate(['/']);
    }
  }

  ngOnInit(): void {
    this.initForm();
    // get return url from route parameters or default to '/'
    this.returnUrl =
      this.route.snapshot.queryParams['returnUrl'.toString()] || '/';

    // Load remembered username
    const remembered = localStorage.getItem('sbaveca_remembered_username');
    if (remembered) {
      this.loginForm.patchValue({
        username: remembered,
        rememberMe: true
      });
    }
  }

  // convenience getter for easy access to form fields
  get f() {
    return this.loginForm.controls;
  }

  initForm() {
    this.loginForm = this.fb.group({
      username: [
        this.defaultAuth.username,
        Validators.compose([
          Validators.required,
          Validators.minLength(3),
          Validators.maxLength(100),
        ]),
      ],
      password: [
        this.defaultAuth.password,
        Validators.compose([
          Validators.required,
          Validators.pattern(/^(?=.*[0-9])(?=.*[^a-zA-Z0-9\s])\S{8,}$/),
        ]),
      ],
      rememberMe: [
        this.defaultAuth.rememberMe
      ]
    });
  }

  submit() {
    this.hasError = false;
    const loginSubscr = this.authService
      .login(this.f.username.value, this.f.password.value)
      .pipe(first())
      .subscribe((user: UserModel | undefined) => {
        if (user) {
          if (this.f.rememberMe.value) {
            localStorage.setItem('sbaveca_remembered_username', this.f.username.value);
          } else {
            localStorage.removeItem('sbaveca_remembered_username');
          }
          // Reset inactivity
          this.authService.resetInactivity();
          this.router.navigate([this.returnUrl]);
        } else {
          this.hasError = true;
        }
      });
    this.unsubscribe.push(loginSubscr);
  }

  loginWithGoogle(event: Event) {
    event.preventDefault();
    this.hasError = false;

    if (typeof google === 'undefined') {
      alert('El SDK de Google no se ha cargado correctamente. Por favor intenta de nuevo.');
      return;
    }

    const client = google.accounts.oauth2.initTokenClient({
      client_id: environment.googleClientId,
      scope: 'email profile openid',
      callback: (response: any) => {
        if (response.access_token) {
          const googleLoginSub = this.authService.loginWithGoogle(response.access_token)
            .pipe(first())
            .subscribe((user) => {
              if (user) {
                this.router.navigate([this.returnUrl]);
              } else {
                this.hasError = true;
              }
            });
          this.unsubscribe.push(googleLoginSub);
        } else {
          this.hasError = true;
        }
      },
    });
    client.requestAccessToken();
  }

  ngOnDestroy() {
    this.unsubscribe.forEach((sb) => sb.unsubscribe());
  }
}
