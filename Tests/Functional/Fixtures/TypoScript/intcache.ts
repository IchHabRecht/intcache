config {
    disableAllHeaderCode = 1
}

lib.intcache.settings.typeNum = 4242
intcache.typeNum = 4242

page = PAGE
page {
    10 = COA_INT
    10 {
        10 = TEXT
        10.value = Hello world!
    }
}

page2 < page
page2 {
    typeNum = 2

    config.intcache = 0
}
