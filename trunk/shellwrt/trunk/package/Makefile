include $(TOPDIR)/rules.mk

PKG_NAME:=shellwrt
PKG_VERSION:=0.0.1
PKG_RELEASE:=1

PKG_BUILD_DIR:=$(BUILD_DIR)/shellwrt-$(PKG_VERSION)
PKG_SOURCE:=shellwrt-$(PKG_VERSION).tar.gz
PKG_SOURCE_URL:=http://home20.inet.tele.dk/o-o/shellwrt
PKG_MD5SUM:=3d7548e90189f9c769dafc4bf983ca76
PKG_CAT:=zcat

include $(TOPDIR)/package/rules.mk

define Package/shellwrt
  SECTION:=base
  CATEGORY:=Utilities
  DEFAULT:=y
  TITLE:=shellwrt shell configuration for OpenWrt
  DESCRIPTION:=shellwrt shell configuration for OpenWrt\\
  URL:=http://home20.inet.tele.dk/shellwrt
endef


define Package/shellwrt/install
###     install -m0755 -d $(1)/usr/sbin
#       install -m0755 $(PKG_BUILD_DIR)/brctl/brctl $(1)/usr/sbin/
endef

$(eval $(call BuildPackage,shellwrt))
