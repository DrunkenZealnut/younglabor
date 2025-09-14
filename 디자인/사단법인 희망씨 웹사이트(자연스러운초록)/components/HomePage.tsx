import { Heart, Users, Handshake, Globe, Star, Leaf, Sprout, TreePine } from "lucide-react";
import { Button } from "./ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "./ui/card";

interface HomePageProps {
  onPageChange: (page: string) => void;
}

export default function HomePage({ onPageChange }: HomePageProps) {
  return (
    <div className="space-y-0 overflow-hidden bg-natural-50">
      {/* Hero Section with natural gradient and floating elements */}
      <section className="relative min-h-screen flex items-center justify-center overflow-hidden">
        {/* Natural background */}
        <div className="absolute inset-0 gradient-natural"></div>
        <div className="absolute inset-0">
          <div className="absolute top-20 left-10 w-32 h-32 bg-lime-400/20 rounded-full blur-xl floating-animation"></div>
          <div className="absolute top-40 right-20 w-24 h-24 bg-green-300/30 rounded-full blur-lg floating-animation" style={{animationDelay: '2s'}}></div>
          <div className="absolute bottom-32 left-1/4 w-40 h-40 bg-lime-300/15 rounded-full blur-2xl floating-animation" style={{animationDelay: '4s'}}></div>
          <div className="absolute top-1/3 right-1/3 w-20 h-20 bg-green-400/20 rounded-full blur-lg floating-animation" style={{animationDelay: '1s'}}></div>
        </div>
        
        <div className="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
          <div className="animate-in fade-in slide-in-from-bottom-8 duration-1000">
            <div className="flex justify-center items-center mb-8">
              <Leaf className="w-12 h-12 text-lime-500 animate-bounce mr-4" />
              <h1 className="text-5xl md:text-7xl text-forest-700 drop-shadow-sm">
                사단법인 <span className="relative">
                  <span className="text-lime-600">희망씨</span>
                  <Sprout className="w-8 h-8 text-lime-500 absolute -top-2 -right-12 animate-bounce-slow" />
                </span>
              </h1>
              <TreePine className="w-12 h-12 text-forest-600 animate-pulse ml-4" />
            </div>
            
            <div className="max-w-4xl mx-auto mb-12">
              <p className="text-xl md:text-2xl text-forest-600 mb-6 leading-relaxed">
                🌱 이웃과 친척과 동료와 경쟁하는 삶이 아닌 더불어 사는 삶을 위하여 🌱
              </p>
              <p className="text-lg md:text-xl text-gray-600 leading-relaxed">
                희망연대노동조합 조합원과 지역주민들이 함께 설립한 따뜻한 법인입니다 🏠
              </p>
            </div>
            
            <div className="flex flex-col sm:flex-row gap-6 justify-center items-center animate-in fade-in slide-in-from-bottom-4 duration-1000 delay-500">
              <Button 
                size="lg" 
                className="bg-lime-500 text-white hover:bg-lime-600 hover:scale-105 transition-all duration-300 shadow-lg text-lg px-8 py-4 rounded-full"
                onClick={() => onPageChange("정기후원(cms)")}
              >
                🌟 후원하기
              </Button>
              <Button 
                variant="outline" 
                size="lg"
                className="border-2 border-lime-500 text-lime-600 hover:bg-lime-50 hover:scale-105 transition-all duration-300 text-lg px-8 py-4 rounded-full"
                onClick={() => onPageChange("희망씨는")}
              >
                🏠 희망씨 알아보기
              </Button>
            </div>
          </div>
        </div>
        
        {/* Floating nature icons */}
        <div className="absolute bottom-20 left-10 text-lime-400/60 floating-animation">
          <Heart className="w-12 h-12" />
        </div>
        <div className="absolute top-20 right-10 text-forest-500/60 floating-animation" style={{animationDelay: '3s'}}>
          <Users className="w-12 h-12" />
        </div>
      </section>

      {/* Vision Section with natural cards */}
      <section className="py-20 bg-gradient-to-b from-natural-100 to-natural-200">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-16 animate-in fade-in slide-in-from-bottom-4 duration-1000">
            <div className="flex justify-center items-center mb-6">
              <Leaf className="w-8 h-8 text-lime-500 mr-3" />
              <h2 className="text-4xl text-forest-700">우리의 비전</h2>
              <Leaf className="w-8 h-8 text-lime-500 ml-3" />
            </div>
            <p className="text-xl text-gray-600">희망씨가 추구하는 건강하고 지속가능한 가치들 🌿</p>
          </div>
          
          <div className="grid md:grid-cols-3 gap-8">
            <Card className="text-center border-0 shadow-lg hover-lift card-glow bg-white overflow-hidden animate-in fade-in slide-in-from-left duration-1000">
              <CardHeader>
                <div className="mx-auto w-20 h-20 bg-gradient-to-br from-pink-400 to-pink-500 rounded-full flex items-center justify-center mb-6 shadow-lg">
                  <Heart className="w-10 h-10 text-white animate-pulse" />
                </div>
                <CardTitle className="text-2xl text-forest-600">🌸 아동권리실현</CardTitle>
              </CardHeader>
              <CardContent>
                <CardDescription className="text-lg text-gray-700 leading-relaxed">
                  모든 아동청소년이 고유한 인격체로서 존중받고 어떠한 이유로도 차별받지 않도록 아동권리실현에 앞장섭니다.
                </CardDescription>
              </CardContent>
            </Card>

            <Card className="text-center border-0 shadow-lg hover-lift card-glow bg-white overflow-hidden animate-in fade-in slide-in-from-bottom duration-1000 delay-200">
              <CardHeader>
                <div className="mx-auto w-20 h-20 bg-gradient-to-br from-lime-400 to-lime-500 rounded-full flex items-center justify-center mb-6 shadow-lg">
                  <Users className="w-10 h-10 text-white animate-bounce-slow" />
                </div>
                <CardTitle className="text-2xl text-forest-600">🤝 나눔연대</CardTitle>
              </CardHeader>
              <CardContent>
                <CardDescription className="text-lg text-gray-700 leading-relaxed">
                  노동자가 자발적 주체가 되어 나눔연대·생활문화연대를 위한 지속가능한 활동을 만들어 갑니다.
                </CardDescription>
              </CardContent>
            </Card>

            <Card className="text-center border-0 shadow-lg hover-lift card-glow bg-white overflow-hidden animate-in fade-in slide-in-from-right duration-1000 delay-400">
              <CardHeader>
                <div className="mx-auto w-20 h-20 bg-gradient-to-br from-green-500 to-forest-600 rounded-full flex items-center justify-center mb-6 shadow-lg">
                  <Handshake className="w-10 h-10 text-white animate-pulse" />
                </div>
                <CardTitle className="text-2xl text-forest-600">🌱 지역연대</CardTitle>
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

      {/* Programs Section with clean white cards */}
      <section className="py-20 bg-white">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-16 animate-in fade-in slide-in-from-bottom-4 duration-1000">
            <h2 className="text-4xl text-forest-700 mb-4">🎯 주요 사업</h2>
            <p className="text-xl text-gray-600">희망씨가 진행하는 다양하고 의미있는 활동들</p>
          </div>
          
          <div className="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
            <Card 
              className="cursor-pointer hover-lift card-glow border border-lime-200 shadow-md bg-white overflow-hidden group animate-in fade-in slide-in-from-left duration-1000"
              onClick={() => onPageChange("국내아동지원사업")}
            >
              <CardHeader>
                <div className="w-12 h-12 bg-gradient-to-br from-pink-400 to-pink-500 rounded-full flex items-center justify-center mb-3 mx-auto group-hover:scale-110 transition-transform duration-300">
                  <Heart className="w-6 h-6 text-white" />
                </div>
                <CardTitle className="text-lg text-forest-600">🏠 국내아동지원사업</CardTitle>
              </CardHeader>
              <CardContent>
                <CardDescription className="text-gray-700">
                  국내 취약계층 아동들을 위한 따뜻한 지원 프로그램을 운영합니다.
                </CardDescription>
              </CardContent>
            </Card>

            <Card 
              className="cursor-pointer hover-lift card-glow border border-lime-200 shadow-md bg-white overflow-hidden group animate-in fade-in slide-in-from-bottom duration-1000 delay-200"
              onClick={() => onPageChange("해외아동지원사업")}
            >
              <CardHeader>
                <div className="w-12 h-12 bg-gradient-to-br from-blue-400 to-blue-500 rounded-full flex items-center justify-center mb-3 mx-auto group-hover:scale-110 transition-transform duration-300">
                  <Globe className="w-6 h-6 text-white" />
                </div>
                <CardTitle className="text-lg text-forest-600">🌍 해외아동지원사업</CardTitle>
              </CardHeader>
              <CardContent>
                <CardDescription className="text-gray-700">
                  해외 지역의 아동들을 위한 국제 지원 활동을 펼칩니다.
                </CardDescription>
              </CardContent>
            </Card>

            <Card 
              className="cursor-pointer hover-lift card-glow border border-lime-200 shadow-md bg-white overflow-hidden group animate-in fade-in slide-in-from-bottom duration-1000 delay-400"
              onClick={() => onPageChange("노동인권사업")}
            >
              <CardHeader>
                <div className="w-12 h-12 bg-gradient-to-br from-lime-500 to-green-500 rounded-full flex items-center justify-center mb-3 mx-auto group-hover:scale-110 transition-transform duration-300">
                  <Users className="w-6 h-6 text-white" />
                </div>
                <CardTitle className="text-lg text-forest-600">⚖️ 노동인권사업</CardTitle>
              </CardHeader>
              <CardContent>
                <CardDescription className="text-gray-700">
                  노동자의 권익 보호와 인권 향상을 위한 활동을 합니다.
                </CardDescription>
              </CardContent>
            </Card>

            <Card 
              className="cursor-pointer hover-lift card-glow border border-lime-200 shadow-md bg-white overflow-hidden group animate-in fade-in slide-in-from-right duration-1000 delay-600"
              onClick={() => onPageChange("소통 및 회원사업")}
            >
              <CardHeader>
                <div className="w-12 h-12 bg-gradient-to-br from-forest-600 to-green-600 rounded-full flex items-center justify-center mb-3 mx-auto group-hover:scale-110 transition-transform duration-300">
                  <Handshake className="w-6 h-6 text-white" />
                </div>
                <CardTitle className="text-lg text-forest-600">💬 소통 및 회원사업</CardTitle>
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

      {/* Support Section with natural gradient */}
      <section className="py-20 bg-natural-100">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="relative overflow-hidden rounded-3xl shadow-2xl animate-in fade-in slide-in-from-bottom-8 duration-1000">
            <div className="absolute inset-0 gradient-primary"></div>
            <div className="absolute inset-0">
              <div className="absolute top-10 left-10 w-32 h-32 bg-white/10 rounded-full blur-xl floating-animation"></div>
              <div className="absolute bottom-10 right-10 w-24 h-24 bg-lime-300/20 rounded-full blur-lg floating-animation" style={{animationDelay: '2s'}}></div>
              <div className="absolute top-1/2 left-1/2 w-40 h-40 bg-green-300/10 rounded-full blur-2xl floating-animation" style={{animationDelay: '4s'}}></div>
            </div>
            
            <div className="relative z-10 p-12 md:p-16 text-center text-white">
              <div className="flex justify-center items-center mb-6">
                <Star className="w-8 h-8 text-lime-300 animate-bounce mr-3" />
                <h2 className="text-4xl md:text-5xl drop-shadow-lg">함께 만들어가는 희망</h2>
                <Star className="w-8 h-8 text-lime-300 animate-bounce ml-3" />
              </div>
              <p className="text-xl md:text-2xl mb-10 opacity-95 drop-shadow-md max-w-3xl mx-auto leading-relaxed">
                🌱 여러분의 소중한 후원이 더 건강하고 지속가능한 세상을 만들어갑니다 💚
              </p>
              <div className="flex flex-col sm:flex-row gap-6 justify-center items-center">
                <Button 
                  size="lg" 
                  className="bg-white text-forest-600 hover:bg-lime-50 hover:scale-110 transition-all duration-300 shadow-xl text-xl px-10 py-5 rounded-full"
                  onClick={() => onPageChange("정기후원(cms)")}
                >
                  🌟 정기후원하기
                </Button>
                <Button 
                  size="lg" 
                  className="border-2 border-white text-white hover:bg-white hover:text-forest-600 hover:scale-110 transition-all duration-300 text-xl px-10 py-5 rounded-full backdrop-blur-sm bg-white/10"
                  onClick={() => onPageChange("일시후원")}
                >
                  💝 일시후원하기
                </Button>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  );
}