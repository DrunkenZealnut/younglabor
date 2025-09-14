import { MapPin, Phone, Mail, Facebook, Instagram, Youtube, Heart, Building } from "lucide-react";
import { Separator } from "./ui/separator";

export default function Footer() {
  return (
    <footer className="relative overflow-hidden">
      {/* Blue gradient background */}
      <div className="absolute inset-0 bg-gradient-to-br from-blue-700 via-blue-800 to-blue-900"></div>
      
      <div className="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div className="grid md:grid-cols-4 gap-8">
          <div className="md:col-span-2">
            <div className="flex items-center mb-6">
              <Building className="w-8 h-8 text-sky-300 mr-3" />
              <h3 className="text-3xl text-white mr-3">사단법인</h3>
              <span className="text-4xl text-sky-300">희망씨</span>
              <Heart className="w-6 h-6 text-pink-300 ml-2" />
            </div>
            <p className="text-gray-300 mb-8 leading-relaxed text-lg">
              이웃과 친척과 동료와 경쟁하는 삶이 아닌 더불어 사는 삶을 위하여 
              희망연대노동조합 조합원과 지역주민들이 함께 설립한 따뜻한 법인입니다.
            </p>
            <div className="flex space-x-4">
              <div className="w-12 h-12 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center hover:scale-110 transition-transform duration-300 cursor-pointer">
                <Facebook className="w-6 h-6 text-white" />
              </div>
              <div className="w-12 h-12 bg-gradient-to-br from-pink-400 to-pink-600 rounded-full flex items-center justify-center hover:scale-110 transition-transform duration-300 cursor-pointer">
                <Instagram className="w-6 h-6 text-white" />
              </div>
              <div className="w-12 h-12 bg-gradient-to-br from-red-400 to-red-600 rounded-full flex items-center justify-center hover:scale-110 transition-transform duration-300 cursor-pointer">
                <Youtube className="w-6 h-6 text-white" />
              </div>
            </div>
          </div>
          
          <div className="bg-white/10 backdrop-blur-sm rounded-xl p-6 border border-sky-300/30">
            <h4 className="text-xl text-white mb-6 flex items-center">
              <MapPin className="w-5 h-5 text-sky-300 mr-2" />
              연락처
            </h4>
            <div className="space-y-4">
              <div className="flex items-start space-x-3 text-gray-300">
                <MapPin className="w-5 h-5 text-sky-300 mt-1 flex-shrink-0" />
                <div>
                  <p className="text-white">서울특별시 중구 을지로 100</p>
                  <p className="text-sm">희망빌딩 3층</p>
                </div>
              </div>
              <div className="flex items-center space-x-3 text-gray-300">
                <Phone className="w-5 h-5 text-green-400" />
                <span>02-1234-5678</span>
              </div>
              <div className="flex items-center space-x-3 text-gray-300">
                <Mail className="w-5 h-5 text-blue-400" />
                <span>info@hopeseed.org</span>
              </div>
            </div>
          </div>
          
          <div className="bg-white/10 backdrop-blur-sm rounded-xl p-6 border border-sky-300/30">
            <h4 className="text-xl text-white mb-6 flex items-center">
              <Heart className="w-5 h-5 text-sky-300 mr-2" />
              후원계좌
            </h4>
            <div className="space-y-3 text-gray-300">
              <div className="bg-white/10 rounded-lg p-3">
                <p className="text-white">국민은행</p>
                <p className="text-lg">123-456-789012</p>
              </div>
              <p className="text-sm">예금주: 사단법인 희망씨</p>
              <div className="mt-4 p-3 bg-gradient-to-r from-sky-500/20 to-blue-500/20 rounded-lg border border-sky-400/30">
                <p className="text-sky-200 flex items-center">
                  <Phone className="w-4 h-4 mr-1" />
                  후원문의: 02-1234-5678
                </p>
              </div>
            </div>
          </div>
        </div>
        
        <Separator className="my-12 bg-white/20" />
        
        <div className="flex flex-col md:flex-row justify-between items-center text-gray-300">
          <p className="flex items-center text-lg">
            <span>&copy; 2024 사단법인 희망씨. All rights reserved.</span>
            <Heart className="w-4 h-4 text-sky-300 mx-2" />
          </p>
          <p className="mt-2 md:mt-0">고유번호: 123-45-67890</p>
        </div>
      </div>
    </footer>
  );
}