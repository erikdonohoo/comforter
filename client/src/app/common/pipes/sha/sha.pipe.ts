import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'sha'
})
export class ShaPipe implements PipeTransform {
  transform(value: string): string {
    if(!value || value === '') {
      return '';
    }
    return `#${value.substr(0, 7)}`;
  }
}
