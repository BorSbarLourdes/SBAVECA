import { Component, OnInit, OnDestroy, ChangeDetectorRef } from '@angular/core';
import { Subscription } from 'rxjs';
import { StateService, Employee } from '../state.service';

@Component({
  selector: 'app-empleados',
  templateUrl: './empleados.component.html',
  styleUrls: []
})
export class EmpleadosComponent implements OnInit, OnDestroy {
  employees: Employee[] = [];
  isModalOpen = false;

  // Form fields
  formId = 0;
  formName = '';
  formLastname = '';
  formRole: 'Administrador/Dueño' | 'Empleado' | 'Cliente Web' = 'Empleado';
  formEmail = '';
  formPhone = '';
  formStatus: 'Activo' | 'Inactivo' = 'Activo';
  formVer = true;
  formEditar = false;
  formBorrar = false;

  private subscriptions: Subscription[] = [];

  constructor(
    private stateService: StateService,
    private cdr: ChangeDetectorRef
  ) {}

  ngOnInit(): void {
    const sub = this.stateService.employees$.subscribe(data => {
      this.employees = data;
      this.cdr.detectChanges();
    });
    this.subscriptions.push(sub);
  }

  openNew() {
    this.formId = 0;
    this.formName = '';
    this.formLastname = '';
    this.formRole = 'Empleado';
    this.formEmail = '';
    this.formPhone = '';
    this.formStatus = 'Activo';
    this.formVer = true;
    this.formEditar = false;
    this.formBorrar = false;
    this.isModalOpen = true;
  }

  editEmployee(emp: Employee) {
    this.formId = emp.id;
    this.formName = emp.name;
    this.formLastname = emp.lastname;
    this.formRole = emp.role;
    this.formEmail = emp.email;
    this.formPhone = emp.phone;
    this.formStatus = emp.status;
    this.formVer = emp.permissions?.ver ?? true;
    this.formEditar = emp.permissions?.editar ?? false;
    this.formBorrar = emp.permissions?.borrar ?? false;
    this.isModalOpen = true;
  }

  deleteLogical(id: number) {
    if (confirm('¿Está seguro de dar de baja lógicamente a este empleado?')) {
      this.stateService.deleteEmployeeLogical(id);
    }
  }

  save() {
    if (!this.formName || !this.formLastname || !this.formEmail) {
      alert('Por favor, complete los campos obligatorios.');
      return;
    }

    const emp: Employee = {
      id: this.formId,
      name: this.formName,
      lastname: this.formLastname,
      role: this.formRole,
      email: this.formEmail,
      phone: this.formPhone,
      status: this.formStatus,
      permissions: {
        ver: this.formVer,
        editar: this.formEditar,
        borrar: this.formBorrar
      }
    };

    this.stateService.saveEmployee(emp);
    this.isModalOpen = false;
  }

  ngOnDestroy(): void {
    this.subscriptions.forEach(sub => sub.unsubscribe());
  }
}
