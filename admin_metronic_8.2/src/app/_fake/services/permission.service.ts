import { Injectable } from '@angular/core';
import { Observable, of } from 'rxjs';
import { StateService } from '../../pages/state.service';

export interface DataTablesResponse {
  draw?: number;
  recordsTotal: number;
  recordsFiltered: number;
  data: any[];
}

export interface IPermissionModel {
  id: number;
  name: string;
  created_at?: string;
  updated_at?: string;
}

@Injectable({
  providedIn: 'root'
})
export class PermissionService {

  constructor(private stateService: StateService) { }

  getPermissions(dataTablesParameters: any): Observable<DataTablesResponse> {
    const searchVal = dataTablesParameters?.search?.value?.toLowerCase() || '';
    let filtered = [...this.stateService.systemPermissions$.value];
    if (searchVal) {
      filtered = filtered.filter(p => p.name.toLowerCase().includes(searchVal));
    }
    const response: DataTablesResponse = {
      draw: dataTablesParameters?.draw || 1,
      recordsTotal: this.stateService.systemPermissions$.value.length,
      recordsFiltered: filtered.length,
      data: filtered.slice(dataTablesParameters?.start || 0, (dataTablesParameters?.start || 0) + (dataTablesParameters?.length || 10))
    };
    return of(response);
  }

  getPermission(id: number): Observable<IPermissionModel> {
    const list = this.stateService.systemPermissions$.value;
    const found = list.find(p => p.id === +id);
    return of(found || { id: 0, name: '' });
  }

  createPermission(permission: IPermissionModel): Observable<IPermissionModel> {
    this.stateService.saveSystemPermission(permission);
    // Find the saved permission to return with its assigned ID
    const list = this.stateService.systemPermissions$.value;
    const found = list[list.length - 1];
    return of(found);
  }

  updatePermission(id: number, permission: IPermissionModel): Observable<IPermissionModel> {
    permission.id = +id;
    this.stateService.saveSystemPermission(permission);
    return of(permission);
  }

  deletePermission(id: number): Observable<void> {
    this.stateService.deleteSystemPermission(+id);
    return of(undefined);
  }
}
