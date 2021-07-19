clean:
	rm -rf build

build:
	mkdir build
	ppm --no-intro --compile="src/KimchiRPC" --directory="build"

update:
	ppm --generate-package="src/KimchiRPC"

install:
	ppm --no-intro --no-prompt --fix-conflict --install="build/net.intellivoid.kimchi_rpc.ppm"

install_fast:
	ppm --no-intro --no-prompt --fix-conflict --skip-dependencies --install="build/net.intellivoid.kimchi_rpc.ppm"