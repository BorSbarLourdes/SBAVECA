import { Component, OnInit } from '@angular/core';
import { AuthService } from '../../../../../modules/auth/services/auth.service';
import { StateService } from '../../../../../pages/state.service';

@Component({
  selector: 'app-sidebar-menu',
  templateUrl: './sidebar-menu.component.html',
  styleUrls: ['./sidebar-menu.component.scss']
})
export class SidebarMenuComponent implements OnInit {

  constructor(
    private authService: AuthService,
    private stateService: StateService
  ) { }

  ngOnInit(): void {
  }

  hasRole(rolesAllowed: number[]): boolean {
    const user = this.authService.currentUserValue;
    if (!user || !user.roles) {
      return false;
    }
    return user.roles.some(role => rolesAllowed.includes(role));
  }

  hasPermission(permissionId: number): boolean {
    const user = this.authService.currentUserValue;
    if (!user) return false;

    // Admin (role ID 1) has all permissions
    if (user.roles && user.roles.includes(1)) {
      return true;
    }

    const systemUsers = this.stateService.systemUsers$.value;
    const foundUser = systemUsers.find(u => u.id === user.id);
    if (!foundUser) return false;

    const roles = this.stateService.systemRoles$.value;
    const userRoles = roles.filter(r => foundUser.roleIds.includes(r.id));

    const permissionIds = new Set<number>();
    userRoles.forEach(r => {
      if (r.permissionIds) {
        r.permissionIds.forEach((pId: number) => permissionIds.add(pId));
      }
    });

    return permissionIds.has(permissionId);
  }
}
