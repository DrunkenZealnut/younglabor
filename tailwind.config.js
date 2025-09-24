/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./**/*.php",
    "./theme/**/*.php",
    "./community/**/*.php", 
    "./admin/**/*.php",
    "./board_templates/**/*.php",
    "./includes/**/*.php",
    "./programs/**/*.php",
    "./about/**/*.php",
    "./donate/**/*.php"
  ],
  theme: {
    extend: {
      colors: {
        // 기존 CSS 변수와 매핑
        'forest-700': '#1f3b2d',
        'forest-600': '#2b5d3e', 
        'forest-500': '#3a7a4e',
        'forest-400': '#4a9660',
        'forest-300': '#5cb372',
        'forest-200': '#7bc995',
        'forest-100': '#a5deb8',
        'forest-50': '#d0f0da',
        'natural-100': '#e8f4e6',
        'natural-200': '#d1e9cd',
        'primary': '#85E546',
        'primary-light': '#9de85b',
        'error': '#ef4444',
        'danger': '#dc2626'
      }
    }
  },
  plugins: [],
  // 안전성을 위한 핵심 클래스 보장 (분석 결과 기반)
  safelist: [
    // 가장 많이 사용되는 클래스들 (상위 100개)
    'text-muted', 'flex', 'items-center', 'border', 'text-sm', 'text-center',
    'text-white', 'rounded-lg', 'justify-content-between', 'rounded', 'justify-center',
    'bg-white', 'w-4', 'h-4', 'transition-all', 'text-danger', 'p-6',
    'text-gray-600', 'text-3xl', 'text-gray-500', 'relative', 'gap-2', 'absolute',
    'w-full', 'rounded-full', 'text-forest-700', 'text-primary', 'text-lg',
    'text-decoration-none', 'shadow-sm', 'flex-1', 'duration-300', 'p-4',
    'text-xl', 'text-forest-600', 'transition-colors', 'border-primary-light',
    'text-xs', 'grid', 'p-3', 'overflow-hidden', 'justify-content-center',
    'bg-light', 'transform', 'justify-between', 'rounded-xl', 'h-8',
    'text-gray-700', 'bg-primary', 'text-2xl',
    
    // 레이아웃 관련 핵심 클래스 (반응형 포함)
    'grid-cols-1', 'grid-cols-2', 'grid-cols-3', 'grid-cols-4',
    'sm:grid-cols-1', 'sm:grid-cols-2', 'sm:grid-cols-3',
    'md:grid-cols-1', 'md:grid-cols-2', 'md:grid-cols-3', 'md:grid-cols-4',
    'lg:grid-cols-1', 'lg:grid-cols-2', 'lg:grid-cols-3', 'lg:grid-cols-4',
    'xl:grid-cols-1', 'xl:grid-cols-2', 'xl:grid-cols-3', 'xl:grid-cols-4',
    'gap-1', 'gap-2', 'gap-3', 'gap-4', 'gap-6', 'gap-8',
    
    // 패딩/마진 클래스들
    'p-1', 'p-2', 'p-3', 'p-4', 'p-5', 'p-6', 'p-8',
    'm-1', 'm-2', 'm-3', 'm-4', 'm-5', 'm-6', 'm-8',
    'px-1', 'px-2', 'px-3', 'px-4', 'px-5', 'px-6', 'px-8',
    'py-1', 'py-2', 'py-3', 'py-4', 'py-5', 'py-6', 'py-8',
    'pt-1', 'pt-2', 'pt-3', 'pt-4', 'pt-5', 'pt-6', 'pt-8',
    'pb-1', 'pb-2', 'pb-3', 'pb-4', 'pb-5', 'pb-6', 'pb-8',
    'pl-1', 'pl-2', 'pl-3', 'pl-4', 'pl-5', 'pl-6', 'pl-8',
    'pr-1', 'pr-2', 'pr-3', 'pr-4', 'pr-5', 'pr-6', 'pr-8',
    'mt-1', 'mt-2', 'mt-3', 'mt-4', 'mt-5', 'mt-6', 'mt-8',
    'mb-1', 'mb-2', 'mb-3', 'mb-4', 'mb-5', 'mb-6', 'mb-8',
    'ml-1', 'ml-2', 'ml-3', 'ml-4', 'ml-5', 'ml-6', 'ml-8',
    'mr-1', 'mr-2', 'mr-3', 'mr-4', 'mr-5', 'mr-6', 'mr-8',
    
    // 크기 클래스들 
    'w-1', 'w-2', 'w-3', 'w-4', 'w-5', 'w-6', 'w-8', 'w-10', 'w-12',
    'h-1', 'h-2', 'h-3', 'h-4', 'h-5', 'h-6', 'h-8', 'h-10', 'h-12',
    'w-full', 'h-full', 'w-auto', 'h-auto',
    'h-48', 'h-56', 'h-64',
    
    // 호버/포커스/액티브 상태
    'hover:bg-forest-700', 'hover:bg-forest-600', 'hover:text-white',
    'hover:bg-gray-100', 'hover:shadow-md', 'hover:border-primary',
    'hover:scale-110', 'hover:scale-105', 'hover:border-forest-600',
    'focus:outline-none', 'focus:ring-2', 'active:scale-95',
    
    // 전환/변환 효과
    'transition-all', 'transition-colors', 'transition-transform',
    'duration-200', 'duration-300', 'ease-in-out',
    'transform', 'scale-110', 'scale-105', '-translate-y-1/2',
    
    // 배경/테두리 클래스들
    'bg-forest-600', 'bg-forest-700', 'bg-forest-100', 
    'bg-gray-50', 'bg-gray-100', 'bg-red-50', 
    'border-forest-200', 'border-forest-600', 'border-error',
    'border-primary', 'border-primary-light',
    'rounded', 'rounded-lg', 'rounded-xl', 'rounded-full',
    
    // 그림자 효과
    'shadow-sm', 'shadow-md', 'shadow-lg', 'shadow-xl',
    
    // 텍스트 관련
    'text-forest-600', 'text-forest-700', 'text-white', 'text-gray-500',
    'text-gray-600', 'text-gray-700', 'text-gray-900', 'text-red-600',
    'font-bold', 'font-semibold', 'font-medium', 'font-normal',
    'text-xs', 'text-sm', 'text-base', 'text-lg', 'text-xl', 'text-2xl', 'text-3xl', 'text-4xl',
    'leading-tight', 'leading-snug', 'leading-normal',
    
    // 위치 관련  
    'absolute', 'relative', 'fixed', 'static',
    'top-0', 'top-1', 'top-2', 'top-4', 'top-1/2',
    'left-0', 'left-1', 'left-2', 'left-4', 'right-0', 'right-1', 'right-2', 'right-4',
    'bottom-0', 'bottom-1', 'bottom-2', 'bottom-4',
    'inset-0', 'z-10', 'z-20', 'z-50', 'z-9999',
    
    // 동적 클래스들 (CSS 변수 기반)
    {
      pattern: /^(bg|text|border)-(forest|natural|primary|error|danger)-(50|100|200|300|400|500|600|700|800|900)$/,
    }
  ]
}