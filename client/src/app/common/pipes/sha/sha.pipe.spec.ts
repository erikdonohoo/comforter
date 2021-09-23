import { ShaPipe } from './sha.pipe';

fdescribe('ShaPipe', () => {
  it('create an instance', () => {
    const pipe = new ShaPipe();
    expect(pipe).toBeTruthy();
  });

  it('should shorten sha correctly', () => {
    const pipe = new ShaPipe();
    expect(pipe.transform('0b11538134a1877626f47c39fafa653c41c1bd2a')).toBe('#0b11538');
  });
});
