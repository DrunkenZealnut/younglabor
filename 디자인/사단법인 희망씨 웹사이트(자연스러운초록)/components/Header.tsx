"use client";

import { useState } from "react";
import { ChevronDown, Leaf } from "lucide-react";

const menuItems = [
  {
    title: "희망씨 소개",
    items: [
      "희망씨는",
      "미션 및 비전", 
      "조직도 및 연혁",
      "오시는길",
      "재정보고"
    ]
  },
  {
    title: "희망씨 사업",
    items: [
      "국내아동지원사업",
      "해외아동지원사업", 
      "노동인권사업",
      "소통 및 회원사업",
      "자원봉사안내"
    ]
  },
  {
    title: "희망씨 후원안내",
    items: [
      "정기후원(cms)",
      "일시후원"
    ]
  },
  {
    title: "커뮤니티",
    items: [
      "공지사항",
      "언론보도",
      "소식지", 
      "갤러리",
      "자료실",
      "네팔나눔연대여행"
    ]
  }
];

interface HeaderProps {
  currentPage: string;
  onPageChange: (page: string) => void;
}

export default function Header({ currentPage, onPageChange }: HeaderProps) {
  const [activeMenu, setActiveMenu] = useState<string | null>(null);

  return (
    <header className="bg-white/95 border-b border-lime-200 sticky top-0 z-50 shadow-sm backdrop-blur-md">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex justify-between items-center h-16">
          <div 
            className="flex items-center cursor-pointer group"
            onClick={() => onPageChange("home")}
          >
            <div className="flex items-center">
              <Leaf className="w-7 h-7 text-lime-500 mr-2 group-hover:rotate-12 transition-transform duration-300" />
              <h1 className="text-2xl text-forest-600 group-hover:text-lime-600 transition-colors duration-300">희망씨</h1>
            </div>
            <span className="ml-2 text-sm text-gray-500 group-hover:text-forest-600 transition-colors">사단법인</span>
          </div>
          
          <nav className="hidden md:flex space-x-1">
            {menuItems.map((menu) => (
              <div
                key={menu.title}
                className="relative"
                onMouseEnter={() => setActiveMenu(menu.title)}
                onMouseLeave={() => setActiveMenu(null)}
              >
                <button className="flex items-center space-x-1 text-forest-600 hover:text-lime-600 py-2 px-3 rounded-lg hover:bg-natural-200 transition-all duration-300">
                  <span>{menu.title}</span>
                  <ChevronDown className="w-4 h-4" />
                </button>
                
                {activeMenu === menu.title && (
                  <div className="absolute top-full left-0 bg-white/95 backdrop-blur-md border border-lime-200 rounded-xl shadow-xl py-3 min-w-48 z-10 animate-in slide-in-from-top-2 duration-200">
                    {menu.items.map((item, index) => (
                      <button
                        key={item}
                        className="block w-full text-left px-4 py-2.5 text-sm text-forest-600 hover:bg-natural-200 hover:text-lime-600 transition-all duration-200 rounded-lg mx-2"
                        onClick={() => onPageChange(item)}
                        style={{
                          animationDelay: `${index * 50}ms`
                        }}
                      >
                        {item}
                      </button>
                    ))}
                  </div>
                )}
              </div>
            ))}
          </nav>
        </div>
      </div>
    </header>
  );
}