import { Injectable } from '@angular/core';
import { Observable, of } from 'rxjs';
import { IRoleModel } from './role.service';
import { StateService } from '../../pages/state.service';

export interface DataTablesResponse {
    draw?: number;
    recordsTotal: number;
    recordsFiltered: number;
    data: any[];
}

export interface IUserModel {
    avatar?: null | string;
    created_at?: string;
    email: string;
    email_verified_at?: string;
    id: number;
    last_login_at?: null | string;
    last_login_ip?: null | string;
    name?: string;
    profile_photo_path?: null | string;
    updated_at?: string;
    password?: string;
    roles?: IRoleModel[];
    role?: string;
}

@Injectable({
    providedIn: 'root'
})
export class UserService {

    constructor(private stateService: StateService) { }

    private mapUserToModel(user: any): IUserModel {
        const rolesList = this.stateService.systemRoles$.value;
        const userRoles = rolesList.filter(r => (user.roleIds || []).includes(r.id)).map(r => ({
            id: r.id,
            name: r.name,
            permissions: [],
            users: []
        }));

        return {
            id: user.id,
            name: user.name,
            email: user.email,
            roles: userRoles,
            role: userRoles.map(r => r.name).join(', '),
            avatar: user.id === 1 ? './assets/media/avatars/300-1.jpg' : user.id === 2 ? './assets/media/avatars/300-6.jpg' : './assets/media/avatars/300-20.jpg',
            created_at: user.created_at,
            last_login_at: user.last_login_at
        };
    }

    getUsers(dataTablesParameters: any): Observable<DataTablesResponse> {
        const searchVal = dataTablesParameters?.search?.value?.toLowerCase() || '';
        let filtered = [...this.stateService.systemUsers$.value];
        if (searchVal) {
            filtered = filtered.filter(u => 
                u.name?.toLowerCase().includes(searchVal) || 
                u.email?.toLowerCase().includes(searchVal) ||
                u.username?.toLowerCase().includes(searchVal)
            );
        }
        const data = filtered.map(u => this.mapUserToModel(u));
        const response: DataTablesResponse = {
            draw: dataTablesParameters?.draw || 1,
            recordsTotal: this.stateService.systemUsers$.value.length,
            recordsFiltered: filtered.length,
            data: data.slice(dataTablesParameters?.start || 0, (dataTablesParameters?.start || 0) + (dataTablesParameters?.length || 10))
        };
        return of(response);
    }

    getUser(id: number): Observable<IUserModel> {
        const list = this.stateService.systemUsers$.value;
        const found = list.find(u => u.id === +id);
        return of(found ? this.mapUserToModel(found) : { id: 0, name: '', email: '', role: '' });
    }

    createUser(user: IUserModel): Observable<IUserModel> {
        const rolesList = this.stateService.systemRoles$.value;
        let roleIds: number[] = [];
        if (user.roles && user.roles.length > 0) {
            roleIds = user.roles.map(r => r.id);
        } else if (user.role) {
            const foundRole = rolesList.find(r => r.name.toLowerCase() === user.role?.toLowerCase());
            if (foundRole) {
                roleIds = [foundRole.id];
            }
        }

        const systemUser = {
            id: 0,
            name: user.name,
            username: user.email.split('@')[0],
            email: user.email,
            password: user.password || 'Password123!',
            roleIds: roleIds,
            last_login_at: new Date().toISOString()
        };

        this.stateService.saveSystemUser(systemUser);
        const saved = this.stateService.systemUsers$.value;
        const last = saved[saved.length - 1];
        return of(this.mapUserToModel(last));
    }

    updateUser(id: number, user: IUserModel): Observable<IUserModel> {
        const list = this.stateService.systemUsers$.value;
        const existing = list.find(u => u.id === +id);

        const rolesList = this.stateService.systemRoles$.value;
        let roleIds: number[] = [];
        if (user.roles && user.roles.length > 0) {
            roleIds = user.roles.map(r => r.id);
        } else if (user.role) {
            const foundRole = rolesList.find(r => r.name.toLowerCase() === user.role?.toLowerCase());
            if (foundRole) {
                roleIds = [foundRole.id];
            }
        }

        const systemUser = {
            id: +id,
            name: user.name || existing?.name || '',
            username: existing?.username || user.email.split('@')[0],
            email: user.email || existing?.email || '',
            password: existing?.password || 'Password123!',
            roleIds: roleIds.length > 0 ? roleIds : (existing?.roleIds || []),
            last_login_at: existing?.last_login_at || new Date().toISOString()
        };

        this.stateService.saveSystemUser(systemUser);
        return of(this.mapUserToModel(systemUser));
    }

    deleteUser(id: number): Observable<void> {
        this.stateService.deleteSystemUser(+id);
        return of(undefined);
    }
}