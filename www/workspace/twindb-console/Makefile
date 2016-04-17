
all: check theme
	sencha app clean
	sencha app build

check:
	@echo "Checking if Sencha Cmd is installed"
	@which sencha || \
		( echo "Please download and install Sencha Cmd http://www.sencha.com/products/sencha-cmd/" ; exit 1 )

theme:
	cd ../packages/twindb-theme/ ; sencha package build
