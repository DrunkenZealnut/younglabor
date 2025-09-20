import { Heart, Users, Handshake, Globe, Building, Phone, Mail } from "lucide-react";
import { Button } from "./ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "./ui/card";

interface HomePageProps {
  onPageChange: (page: string) => void;
}

export default function HomePage({ onPageChange }: HomePageProps) {
  return (
    <div className="space-y-0 overflow-hidden bg-sky-50">
      {/* Hero Section */}
      <section className="relative min-h-screen flex items-center justify-center overflow-hidden">
        <div className="absolute inset-0 gradient-natural"></div>
        
        <div className="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
          <div>
            <div className="flex justify-center items-center mb-8">
              <h1 className="text-5xl md:text-7xl text-blue-700 font-bold">
                사단법인 <span className="text-sky-500">희망씨</span>
              </h1>
            </div>
            
            <div className="max-w-4xl mx-auto mb-12">
              <p className="text-xl md:text-2xl text-blue-600 mb-6 leading-relaxed font-medium">
                이웃과 친척과 동료와 경쟁하는 삶이 아닌 더불어 사는 삶을 위하여
              </p>
              <p className="text-lg md:text-xl text-gray-600 leading-relaxed">
                희망연대노동조합 조합원과 지역주민들이 함께 설립한 따뜻한 법인입니다
              </p>
            </div>
            
            <div className="flex flex-col sm:flex-row gap-6 justify-center items-center">
              <Button 
                size="lg" 
                className="bg-blue-600 text-white hover:bg-sky-500 hover:scale-105 transition-all duration-300 shadow-lg text-lg px-8 py-4 rounded-lg"
                onClick={() => onPageChange("정기후원(cms)")}
              >
                후원하기
              </Button>
              <Button 
                variant="outline" 
                size="lg"
                className="border-2 border-blue-600 text-blue-600 hover:bg-sky-100 hover:scale-105 transition-all duration-300 text-lg px-8 py-4 rounded-lg"
                onClick={() => onPageChange("희망씨는")}
              >
                희망씨 알아보기
              </Button>
            </div>
          </div>
        </div>
      </section>

      {/* Vision Section */}
      <section className="py-20 bg-white">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-16">
            <h2 className="text-4xl text-blue-700 font-bold mb-6">우리의 비전</h2>
            <p className="text-xl text-gray-600">희망씨가 추구하는 건강하고 지속가능한 가치들</p>
          </div>
          
          <div className="grid md:grid-cols-3 gap-8">
            <Card className="text-center border border-blue-200 shadow-lg bg-white overflow-hidden hover-lift">
              <CardHeader>
                <div className="mx-auto w-20 h-20 bg-gradient-to-br from-pink-400 to-pink-500 rounded-full flex items-center justify-center mb-6 shadow-lg">
                  <Heart className="w-10 h-10 text-white" />
                </div>
                <CardTitle className="text-2xl text-blue-600">아동권리실현</CardTitle>
              </CardHeader>
              <CardContent>
                <CardDescription className="text-lg text-gray-700 leading-relaxed">
                  모든 아동청소년이 고유한 인격체로서 존중받고 어떠한 이유로도 차별받지 않도록 아동권리실현에 앞장섭니다.
                </CardDescription>
              </CardContent>
            </Card>

            <Card className="text-center border border-blue-200 shadow-lg bg-white overflow-hidden hover-lift">
              <CardHeader>
                <div className="mx-auto w-20 h-20 bg-gradient-to-br from-sky-400 to-sky-500 rounded-full flex items-center justify-center mb-6 shadow-lg">
                  <Users className="w-10 h-10 text-white" />
                </div>
                <CardTitle className="text-2xl text-blue-600">나눔연대</CardTitle>
              </CardHeader>
              <CardContent>
                <CardDescription className="text-lg text-gray-700 leading-relaxed">
                  노동자가 자발적 주체가 되어 나눔연대·생활문화연대를 위한 지속가능한 활동을 만들어 갑니다.
                </CardDescription>
              </CardContent>
            </Card>

            <Card className="text-center border border-blue-200 shadow-lg bg-white overflow-hidden hover-lift">
              <CardHeader>
                <div className="mx-auto w-20 h-20 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center mb-6 shadow-lg">
                  <Handshake className="w-10 h-10 text-white" />
                </div>
                <CardTitle className="text-2xl text-blue-600">지역연대</CardTitle>
              </CardHeader>
              <CardContent>
                <CardDescription className="text-lg text-gray-700 leading-relaxed">
                  지역사회와 함께 아래로 향한 연대 일터와 삶터를 바꾸기 위한 활동에 함께 합니다.
                </CardDescription>
              </CardContent>
            </Card>
          </div>
        </div>
      </section>

      {/* Programs Section */}
      <section className="py-20 bg-sky-50">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-16">
            <h2 className="text-4xl text-blue-700 font-bold mb-4">주요 사업</h2>
            <p className="text-xl text-gray-600">희망씨가 진행하는 다양하고 의미있는 활동들</p>
          </div>
          
          <div className="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
            <Card 
              className="cursor-pointer hover-lift border border-blue-200 shadow-md bg-white overflow-hidden group"
              onClick={() => onPageChange("국내아동지원사업")}
            >
              <CardHeader>
                <div className="w-12 h-12 bg-gradient-to-br from-pink-400 to-pink-500 rounded-full flex items-center justify-center mb-3 mx-auto group-hover:scale-110 transition-transform duration-300">
                  <Heart className="w-6 h-6 text-white" />
                </div>
                <CardTitle className="text-lg text-blue-600">국내아동지원사업</CardTitle>
              </CardHeader>
              <CardContent>
                <CardDescription className="text-gray-700">
                  국내 취약계층 아동들을 위한 따뜻한 지원 프로그램을 운영합니다.
                </CardDescription>
              </CardContent>
            </Card>

            <Card 
              className="cursor-pointer hover-lift border border-blue-200 shadow-md bg-white overflow-hidden group"
              onClick={() => onPageChange("해외아동지원사업")}
            >
              <CardHeader>
                <div className="w-12 h-12 bg-gradient-to-br from-blue-400 to-blue-500 rounded-full flex items-center justify-center mb-3 mx-auto group-hover:scale-110 transition-transform duration-300">
                  <Globe className="w-6 h-6 text-white" />
                </div>
                <CardTitle className="text-lg text-blue-600">해외아동지원사업</CardTitle>
              </CardHeader>
              <CardContent>
                <CardDescription className="text-gray-700">
                  해외 지역의 아동들을 위한 국제 지원 활동을 펼칩니다.
                </CardDescription>
              </CardContent>
            </Card>

            <Card 
              className="cursor-pointer hover-lift border border-blue-200 shadow-md bg-white overflow-hidden group"
              onClick={() => onPageChange("노동인권사업")}
            >
              <CardHeader>
                <div className="w-12 h-12 bg-gradient-to-br from-sky-500 to-blue-500 rounded-full flex items-center justify-center mb-3 mx-auto group-hover:scale-110 transition-transform duration-300">
                  <Users className="w-6 h-6 text-white" />
                </div>
                <CardTitle className="text-lg text-blue-600">노동인권사업</CardTitle>
              </CardHeader>
              <CardContent>
                <CardDescription className="text-gray-700">
                  노동자의 권익 보호와 인권 향상을 위한 활동을 합니다.
                </CardDescription>
              </CardContent>
            </Card>

            <Card 
              className="cursor-pointer hover-lift border border-blue-200 shadow-md bg-white overflow-hidden group"
              onClick={() => onPageChange("소통 및 회원사업")}
            >
              <CardHeader>
                <div className="w-12 h-12 bg-gradient-to-br from-blue-600 to-blue-700 rounded-full flex items-center justify-center mb-3 mx-auto group-hover:scale-110 transition-transform duration-300">
                  <Handshake className="w-6 h-6 text-white" />
                </div>
                <CardTitle className="text-lg text-blue-600">소통 및 회원사업</CardTitle>
              </CardHeader>
              <CardContent>
                <CardDescription className="text-gray-700">
                  회원들과의 소통과 참여를 위한 다양한 프로그램을 운영합니다.
                </CardDescription>
              </CardContent>
            </Card>
          </div>
        </div>
      </section>

      {/* Support Section */}
      <section className="py-20 bg-white">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="relative overflow-hidden rounded-3xl shadow-2xl">
            <div className="absolute inset-0 gradient-primary"></div>
            
            <div className="relative z-10 p-12 md:p-16 text-center text-white">
              <h2 className="text-4xl md:text-5xl font-bold mb-6">함께 만들어가는 희망</h2>
              <p className="text-xl md:text-2xl mb-10 opacity-95 max-w-3xl mx-auto leading-relaxed">
                여러분의 소중한 후원이 더 건강하고 지속가능한 세상을 만들어갑니다
              </p>
              <div className="flex flex-col sm:flex-row gap-6 justify-center items-center">
                <Button 
                  size="lg" 
                  className="bg-white text-blue-600 hover:bg-sky-50 hover:scale-110 transition-all duration-300 shadow-xl text-xl px-10 py-5 rounded-lg"
                  onClick={() => onPageChange("정기후원(cms)")}
                >
                  정기후원하기
                </Button>
                <Button 
                  size="lg" 
                  className="border-2 border-white text-white hover:bg-white hover:text-blue-600 hover:scale-110 transition-all duration-300 text-xl px-10 py-5 rounded-lg backdrop-blur-sm bg-white/10"
                  onClick={() => onPageChange("일시후원")}
                >
                  일시후원하기
                </Button>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Contact Section */}
      <section className="py-16 bg-sky-50">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-12">
            <h2 className="text-3xl text-blue-700 font-bold mb-4">문의하기</h2>
            <p className="text-lg text-gray-600">언제든 희망씨에 연락해주세요</p>
          </div>
          
          <div className="grid md:grid-cols-3 gap-8 max-w-4xl mx-auto">
            <Card className="text-center border border-blue-200 shadow-md bg-white">
              <CardHeader>
                <Building className="w-8 h-8 text-blue-500 mx-auto mb-2" />
                <CardTitle className="text-lg text-blue-600">주소</CardTitle>
              </CardHeader>
              <CardContent>
                <p className="text-gray-700">서울특별시 중구 을지로 100</p>
                <p className="text-gray-600 text-sm">희망빌딩 3층</p>
              </CardContent>
            </Card>

            <Card className="text-center border border-blue-200 shadow-md bg-white">
              <CardHeader>
                <Phone className="w-8 h-8 text-blue-500 mx-auto mb-2" />
                <CardTitle className="text-lg text-blue-600">전화</CardTitle>
              </CardHeader>
              <CardContent>
                <p className="text-gray-700">02-1234-5678</p>
                <p className="text-gray-600 text-sm">평일 09:00 - 18:00</p>
              </CardContent>
            </Card>

            <Card className="text-center border border-blue-200 shadow-md bg-white">
              <CardHeader>
                <Mail className="w-8 h-8 text-blue-500 mx-auto mb-2" />
                <CardTitle className="text-lg text-blue-600">이메일</CardTitle>
              </CardHeader>
              <CardContent>
                <p className="text-gray-700">info@hopeseed.org</p>
                <p className="text-gray-600 text-sm">언제든 문의하세요</p>
              </CardContent>
            </Card>
          </div>
        </div>
      </section>
    </div>
  );
}