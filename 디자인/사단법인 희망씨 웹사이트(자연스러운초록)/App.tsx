"use client";

import { useState } from "react";
import Header from "./components/Header";
import HomePage from "./components/HomePage";
import ContentPage from "./components/ContentPage";
import Footer from "./components/Footer";

export default function App() {
  const [currentPage, setCurrentPage] = useState("home");

  const handlePageChange = (page: string) => {
    setCurrentPage(page);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  return (
    <div className="min-h-screen flex flex-col bg-natural-50">
      <Header currentPage={currentPage} onPageChange={handlePageChange} />
      
      <main className="flex-1">
        {currentPage === "home" ? (
          <HomePage onPageChange={handlePageChange} />
        ) : (
          <ContentPage page={currentPage} onPageChange={handlePageChange} />
        )}
      </main>
      
      <Footer />
    </div>
  );
}