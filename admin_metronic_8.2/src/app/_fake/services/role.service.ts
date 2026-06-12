import { Injectable } from '@angular/core';
import { Observable, of } from 'rxjs';
import { IPermissionModel } from './permission.service';
import { IUserModel } from './user-service';
import { StateService } from '../../pages/state.service';

export interface DataTablesResponse {
  draw?: number;
  recordsTotal: number;
  recordsFiltered: number;
  data: any[];
}

export interface IRoleModel {
  id: number;
  name: string;
  created_at?: string;
  updated_at?: string;
  permissions: IPermissionModel[];
  users: IUserModel[];
}

@Injectable({
  providedIn: 'root'
})
export class RoleService {

  constructor(private stateService: StateService) { }

  private mapRoleToModel(role: any): IRoleModel {
    const permissionsList = this.stateService.systemPermissions$.value;
    const usersList = this.stateService.systemUsers$.value;

    const rolePermissions = permissionsList.filter(p => (role.permissionIds || []).includes(p.id));
    const roleUsers = usersList.filter(u => (u.roleIds || []).includes(role.id)).map(u => ({
      id: u.id,
      name: u.name,
      email: u.email
    }));

    return {
      id: role.id,
      name: role.name,
      permissions: rolePermissions,
      users: roleUsers
    };
  }

  getUsers(id: number, dataTablesParameters: any): Observable<DataTablesResponse> {
    const usersList = this.stateService.systemUsers$.value;
    const filtered = usersList.filter(u => (u.roleIds || []).includes(+id));
    const response: DataTablesResponse = {
      recordsTotal: filtered.length,
      recordsFiltered: filtered.length,
      data: filtered.map(u => ({
        id: u.id,
        name: u.name,
        email: u.email,
        roles: this.stateService.systemRoles$.value.filter(r => u.roleIds.includes(r.id))
      }))
    };
    return of(response);
  }

  getRoles(dataTablesParameters?: any): Observable<DataTablesResponse> {
    const searchVal = dataTablesParameters?.search?.value?.toLowerCase() || '';
    let filtered = [...this.stateService.systemRoles$.value];
    if (searchVal) {
      filtered = filtered.filter(r => r.name.toLowerCase().includes(searchVal));
    }
    const data = filtered.map(r => this.mapRoleToModel(r));
    const response: DataTablesResponse = {
      draw: dataTablesParameters?.draw || 1,
      recordsTotal: this.stateService.systemRoles$.value.length,
      recordsFiltered: filtered.length,
      data: data
    };
    return of(response);
  }

  getRole(id: number): Observable<IRoleModel> {
    const list = this.stateService.systemRoles$.value;
    const found = list.find(r => r.id === +id);
    return of(found ? this.mapRoleToModel(found) : { id: 0, name: '', permissions: [], users: [] });
  }

  createRole(role: IRoleModel): Observable<IRoleModel> {
    const systemRole = {
      id: 0,
      name: role.name,
      permissionIds: role.permissions ? role.permissions.map(p => p.id) : []
    };
    this.stateService.saveSystemRole(systemRole);
    const saved = this.stateService.systemRoles$.value;
    const last = saved[saved.length - 1];
    return of(this.mapRoleToModel(last));
  }

  updateRole(id: number, role: IRoleModel): Observable<IRoleModel> {
    const systemRole = {
      id: +id,
      name: role.name,
      permissionIds: role.permissions ? role.permissions.map(p => p.id) : []
    };
    this.stateService.saveSystemRole(systemRole);
    return of(this.mapRoleToModel(systemRole));
  }

  deleteRole(id: number): Observable<void> {
    this.stateService.deleteSystemRole(+id);
    return of(undefined);
  }

  deleteUser(role_id: number, user_id: number): Observable<void> {
    const users = [...this.stateService.systemUsers$.value];
    const idx = users.findIndex(u => u.id === +user_id);
    if (idx !== -1) {
      users[idx].roleIds = (users[idx].roleIds || []).filter((rId: number) => rId !== +role_id);
      this.stateService.saveSystemUser(users[idx]);
    }
    return of(undefined);
  }
}
