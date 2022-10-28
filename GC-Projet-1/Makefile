CC=gcc
CFLAGS=-g -Wall -Wmissing-prototypes
LDFLAGS=-g
objects := $(patsubst %.c,%.o,$(wildcard *.c))
all: main
clean:
	-rm $(objects)
	-rm main
main: $(objects)
# gcc -MM *.c pour obtenir les deux lignes ci-dessous
graphe.o: graphe.c graphe.h
main.o: main.c graphe.h
